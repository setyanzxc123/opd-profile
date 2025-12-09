<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * TinyMCE Image Upload Controller
 * 
 * Handles image uploads from TinyMCE editor for rich text content
 * Used in Profile (greeting), PPID (about, tasks_functions), News, etc.
 */
class TinyMCEUpload extends BaseController
{
    private const UPLOAD_DIR = 'uploads/tinymce';
    private const MAX_FILE_SIZE = 5120; // 5MB in KB
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/jpg',
        'image/pjpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ];

    /**
     * Upload image from TinyMCE editor
     * 
     * TinyMCE expects a JSON response with:
     * - Success: { "location": "http://example.com/path/to/image.jpg" }
     * - Error: { "error": { "message": "Error message here" } }
     */
    public function upload(): ResponseInterface
    {
        // Check if user is logged in (Shield Auth)
        helper('auth');
        if (!auth('session')->loggedIn()) {
            return $this->errorResponse('Unauthorized. Please login first.');
        }

        $file = $this->request->getFile('file');

        if (!$file || !$file->isValid()) {
            return $this->errorResponse('No valid file uploaded.');
        }

        // Validate file size
        if ($file->getSizeByUnit('kb') > self::MAX_FILE_SIZE) {
            return $this->errorResponse('File too large. Maximum size is 5MB.');
        }

        // Validate extension
        $extension = strtolower($file->getClientExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return $this->errorResponse('Invalid file type. Allowed: ' . implode(', ', self::ALLOWED_EXTENSIONS));
        }

        // Validate MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIMES)) {
            return $this->errorResponse('Invalid file type.');
        }

        // Create upload directory if not exists
        $uploadPath = FCPATH . self::UPLOAD_DIR;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Generate unique filename with date prefix for organization
        $datePrefix = date('Y/m');
        $datePath = $uploadPath . '/' . $datePrefix;
        if (!is_dir($datePath)) {
            mkdir($datePath, 0755, true);
        }

        // Generate unique filename
        $newName = $file->getRandomName();

        try {
            $file->move($datePath, $newName);
            
            // Return the URL for TinyMCE
            $imageUrl = base_url(self::UPLOAD_DIR . '/' . $datePrefix . '/' . $newName);
            
            return $this->response->setJSON([
                'location' => $imageUrl
            ]);
        } catch (\Exception $e) {
            log_message('error', '[TinyMCEUpload] Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to upload file. Please try again.');
        }
    }

    /**
     * Return error response in TinyMCE expected format
     */
    private function errorResponse(string $message): ResponseInterface
    {
        return $this->response->setJSON([
            'error' => [
                'message' => $message
            ]
        ]);
    }
}
