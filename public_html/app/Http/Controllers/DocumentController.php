<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Contracts\AIService;

class DocumentController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:txt,pdf,doc,docx,md|max:10240', // 10MB max
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            $file = $request->file('file');
            $content = $this->extractTextFromFile($file);
            
            if (empty($content)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Could not extract text from file'
                ], 400);
            }

            // Generate embedding for the content
            $embedding = $this->aiService->embed($content);
            
            // Save to database
            $document = DB::table('ai_documents')->insertGetId([
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
                'content' => $content,
                'embedding' => json_encode($embedding),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded and processed successfully',
                'document_id' => $document
            ]);

        } catch (\Exception $e) {
            Log::error('Document upload error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to process document'
            ], 500);
        }
    }

    public function list()
    {
        try {
            $documents = DB::table('ai_documents')
                ->select(['id', 'title', 'category', 'description', 'file_name', 'file_size', 'created_at'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'documents' => $documents
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch documents'
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $deleted = DB::table('ai_documents')->where('id', $id)->delete();
            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Document not found'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete document'
            ], 500);
        }
    }

    protected function extractTextFromFile($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $content = '';

        try {
            switch ($extension) {
                case 'txt':
                case 'md':
                    $content = file_get_contents($file->getPathname());
                    break;
                    
                case 'pdf':
                    // For PDF parsing, you might want to use a library like smalot/pdfparser
                    // For now, return empty and suggest manual text input
                    $content = '';
                    break;
                    
                case 'doc':
                case 'docx':
                    // For Word documents, you might want to use PhpOffice/PhpWord
                    // For now, return empty and suggest manual text input
                    $content = '';
                    break;
                    
                default:
                    $content = '';
            }

            // Clean and normalize text
            $content = trim($content);
            $content = preg_replace('/\s+/', ' ', $content); // Normalize whitespace
            
            return $content;

        } catch (\Exception $e) {
            Log::error('File text extraction error', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }
}