<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\SupabaseDB;

class WebsiteManagementController extends Controller
{
    private $supabaseUrl;
    private $supabaseKey;
    private $table = 'website_config';

    public function __construct()
    {
        $this->supabaseUrl = getenv('SUPABASE_URL');
        $this->supabaseKey = getenv('SUPABASE_SERVICE_ROLE_KEY') ?: getenv('SUPABASE_API_KEY');
        
        log_message('info', '=== WebsiteManagementController Initialized ===');
    }

    private function supabaseRequest($method, $endpoint, $data = null, $queryParams = [])
    {
        if (empty($this->supabaseUrl) || empty($this->supabaseKey)) {
            log_message('error', 'Supabase credentials not configured');
            return ['error' => 'Supabase credentials not configured'];
        }

        $url = rtrim($this->supabaseUrl, '/') . '/rest/v1/' . $endpoint;
        
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $headers = [
            'apikey: ' . $this->supabaseKey,
            'Authorization: Bearer ' . $this->supabaseKey,
            'Content-Type: application/json',
            'Accept: application/json',
            'Prefer: return=representation'
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        if ($data !== null) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            log_message('info', 'Request Body: ' . $jsonData);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        log_message('info', 'Response Code: ' . $httpCode);
        log_message('info', 'Response Body: ' . $response);

        if ($error) {
            log_message('error', 'cURL Error: ' . $error);
            return ['error' => $error];
        }

        if ($httpCode >= 400) {
            log_message('error', 'HTTP Error ' . $httpCode . ': ' . $response);
            return ['error' => 'HTTP Error ' . $httpCode, 'response' => $response];
        }

        return json_decode($response, true);
    }

    public function index()
    {
        // Get website configuration from Supabase
        $config = $this->supabaseRequest('GET', $this->table, null, [
            'limit' => 1
        ]);
        
        $websiteConfig = null;
        if (is_array($config) && isset($config[0])) {
            $websiteConfig = $config[0];
        }

        return view('management_website', [
            'websiteConfig' => $websiteConfig
        ]);
    }

    public function update()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }

        $siteName = $this->request->getPost('site_name');

        // Get the ID of the first record FIRST
        $config = $this->supabaseRequest('GET', $this->table, null, [
            'limit' => 1,
            'select' => 'id'
        ]);
        
        if (!is_array($config) || !isset($config[0]) || !isset($config[0]['id'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Konfigurasi website tidak ditemukan'
            ]);
        }

        $id = $config[0]['id'];

        // Handle image uploads
        $images = [];
        $imageFields = ['navbar_logo', 'homepage_logo', 'login_background_image'];

        foreach ($imageFields as $field) {
            $file = $this->request->getFile($field);
            
            if ($file && $file->isValid() && !$file->hasMoved()) {
                // Delete old file if exists
                $fullConfig = $this->supabaseRequest('GET', $this->table, null, ['limit' => 1]);
                if (is_array($fullConfig) && isset($fullConfig[0][$field]) && !empty($fullConfig[0][$field])) {
                    $oldFile = FCPATH . $fullConfig[0][$field];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                // Save new file
                $newName = $field . '_' . time() . '.' . $file->getExtension();
                $file->move(FCPATH . 'uploads', $newName);
                $images[$field] = '/uploads/' . $newName;
            }
        }

        $updateData = [
            'site_name' => $siteName
        ];

        // Merge with uploaded images
        $updateData = array_merge($updateData, $images);

        $result = $this->supabaseRequest('PATCH', $this->table . "?id=eq.{$id}", $updateData);

        if (is_null($result) || (isset($result['error']) && $result['error'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengupdate konfigurasi website'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Konfigurasi website berhasil diperbarui'
        ]);
    }
}
