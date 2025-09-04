<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use Smalot\PdfParser\Parser;
use thiagoalessio\TesseractOCR\TesseractOCR;

class PdfImportController extends Controller
{
    // ✅ Show the upload form (GET request)
    public function showForm()
    {
        return view('pdf_upload'); // shows your Blade form
    }

    // ✅ Handle file upload and import (POST request)
    public function importFromUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        // Save file temporarily
        $filePath = $file->store('imports');
        $fullPath = storage_path('app/' . $filePath);

        $text = '';

        try {
            if ($extension === 'pdf') {
                // Parse PDF
                $parser = new Parser();
                $pdf = $parser->parseFile($fullPath);
                $text = $pdf->getText();
            } else {
                // Parse image with OCR
                $text = (new TesseractOCR($fullPath))
                    ->executable('C:/Program Files/Tesseract-OCR/tesseract.exe')
                    ->lang('eng')
                    ->run();
            }
        } catch (\Exception $e) {
            return back()->withErrors(['file' => '❌ Failed to extract text: ' . $e->getMessage()]);
        }

        // 🔎 Debugging: log extracted text
        \Log::info("Extracted text:\n" . $text);

        $lines = preg_split('/\r\n|\r|\n/', $text);
        $inserted = 0;
        $contact = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // More flexible matching
            if (preg_match('/name/i', $line)) {
                $contact['name'] = trim(preg_replace('/name[:\-\s]*/i', '', $line));
            } elseif (preg_match('/email/i', $line)) {
                $contact['email'] = trim(preg_replace('/email[:\-\s]*/i', '', $line));
            } elseif (preg_match('/phone|contact|mobile/i', $line)) {
                $contact['phone'] = trim(preg_replace('/(phone|contact|mobile)[:\-\s]*/i', '', $line));
            }

            // ✅ Save only when we have name + email
            if (!empty($contact['name']) && !empty($contact['email'])) {
                \Log::info('Saving contact: ', $contact); // debug

                $existing = Contact::where('email', $contact['email'])->first();

                if ($existing) {
                    $existing->update([
                        'name'  => $contact['name'],
                        'phone' => $contact['phone'] ?? null,
                    ]);
                } else {
                    Contact::create([
                        'name'  => $contact['name'],
                        'email' => $contact['email'],
                        'phone' => $contact['phone'] ?? null,
                    ]);
                }

                $inserted++;
                $contact = []; // reset
            }
        }

        return redirect()->back()->with('success', "✅ Imported/Updated {$inserted} contacts successfully!");
    }
}
