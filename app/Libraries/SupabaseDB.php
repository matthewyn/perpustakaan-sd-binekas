<?php

namespace App\Libraries;

class SupabaseDB
{
    private $url;
    private $key;

    public function __construct()
    {
        $this->url = getenv('SUPABASE_URL');  // misal: https://your-project.supabase.co
        $this->key = getenv('SUPABASE_ANON_KEY');
    }

    // 🔹 GET request
    public function get($table, $params = [])
    {
        $query = http_build_query($params);
        $endpoint = "{$this->url}/rest/v1/{$table}?{$query}";

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->key}",
            "Authorization: Bearer {$this->key}",
            "Content-Type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    // 🔹 POST request
    public function post($table, $data)
    {
        $endpoint = "{$this->url}/rest/v1/{$table}";

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->key}",
            "Authorization: Bearer {$this->key}",
            "Content-Type: application/json",
            "Prefer: return=representation"  // supaya respon berisi data baru
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    // 🔹 PATCH/UPDATE
    public function patch($table, $data, $filter)
    {
        $endpoint = "{$this->url}/rest/v1/{$table}?{$filter}";

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->key}",
            "Authorization: Bearer {$this->key}",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    // 🔹 DELETE
    public function delete($table, $filter)
    {
        $endpoint = "{$this->url}/rest/v1/{$table}?{$filter}";

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->key}",
            "Authorization: Bearer {$this->key}",
            "Content-Type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}