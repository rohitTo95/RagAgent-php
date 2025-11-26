<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use NeuronAI\RAG\DataLoader\FileDataLoader;
use NeuronAI\RAG\DataLoader\PdfReader;
use App\Neuron\MyChatBot;
use App\Models\Document;

class DocumentController extends Controller
{
    /**
     * Handle document upload and process it for RAG
     */
    public function upload(Request $request)
    {
        try {
            // Validate the uploaded file
            $request->validate([
                'document' => 'required|file|mimes:pdf|max:20480', // Max 20MB
            ]);

            $file = $request->file('document');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Return streaming response for progress updates
            return response()->stream(function () use ($file, $filename) {
                header('Content-Type: text/event-stream');
                header('Cache-Control: no-cache');
                header('Connection: keep-alive');
                header('X-Accel-Buffering: no');

                try {
                    // Store file
                    echo "data: " . json_encode([
                        'progress' => 10,
                        'message' => 'Saving file to storage...'
                    ]) . "\n\n";
                    if (ob_get_level() > 0) ob_flush();
                    flush();

                    $path = $file->storeAs('documents', $filename);
                    $fullPath = Storage::path($path);

                    //  Load PDF 
                    echo "data: " . json_encode([
                        'progress' => 30,
                        'message' => 'Reading PDF document...'
                    ]) . "\n\n";
                    if (ob_get_level() > 0) ob_flush();
                    flush();

                    $documents = FileDataLoader::for(dirname($fullPath))
                        ->addReader('pdf', new PdfReader())
                        ->getDocuments();

                    $totalChunks = count($documents);

                    // Create embeddings and upload
                    echo "data: " . json_encode([
                        'progress' => 40,
                        'message' => "Processing {$totalChunks} chunks..."
                    ]) . "\n\n";
                    if (ob_get_level() > 0) ob_flush();
                    flush();

                    $agent = MyChatBot::make();
                    $vectorStore = $agent->vectorStore();
                    $embeddingsProvider = $agent->embeddings();

                    foreach ($documents as $index => $document) {
                        // Generate embedding
                        $document->embedding = $embeddingsProvider->embedText($document->content);
                        
                        // Add metadata
                        $document->addMetadata('filename', $filename);
                        $document->addMetadata('chunk_index', $index);
                        
                        // Store in vector database
                        $vectorStore->addDocument($document);

                        // Update progress
                        $chunkProgress = 40 + (($index + 1) / $totalChunks) * 50;
                        echo "data: " . json_encode([
                            'progress' => round($chunkProgress),
                            'message' => "Uploading chunk " . ($index + 1) . " of {$totalChunks}..."
                        ]) . "\n\n";
                        if (ob_get_level() > 0) ob_flush();
                        flush();
                    }

                    // Delete file from filesystem
                    echo "data: " . json_encode([
                        'progress' => 95,
                        'message' => 'Cleaning up...'
                    ]) . "\n\n";
                    if (ob_get_level() > 0) ob_flush();
                    flush();

                    Storage::delete($path);

                    // Save document record to database
                    Document::create([
                        'filename' => $filename,
                        'chunks' => $totalChunks
                    ]);

                    echo "data: " . json_encode([
                        'progress' => 100,
                        'message' => 'Document uploaded successfully!',
                        'success' => true,
                        'filename' => $filename,
                        'chunks' => $totalChunks
                    ]) . "\n\n";
                    if (ob_get_level() > 0) ob_flush();
                    flush();

                } catch (\Exception $e) {
                    echo "data: " . json_encode([
                        'error' => true,
                        'message' => 'Upload failed: ' . $e->getMessage()
                    ]) . "\n\n";
                    if (ob_get_level() > 0) ob_flush();
                    flush();
                }
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'X-Accel-Buffering' => 'no'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Get list of uploaded documents
     */
    public function list(): JsonResponse
    {
        try {
            $documents = Document::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'documents' => $documents->map(function($doc) {
                    return [
                        'name' => $doc->filename,
                        'chunks' => $doc->chunks,
                        'uploaded_at' => $doc->created_at->timestamp
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve documents: ' . $e->getMessage()
            ], 500);
        }
    }
}
