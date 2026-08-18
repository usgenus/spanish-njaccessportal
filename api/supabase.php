<?php
/**
 * Lightweight Supabase REST API Client (PHP cURL)
 */

class SupabaseClient {
    private $url;
    private $key;

    public function __construct($url, $key) {
        $this->url = rtrim($url, '/');
        $this->key = $key;
    }

    public function isConfigured() {
        return !empty($this->url) && !empty($this->key) && strpos($this->url, 'http') === 0;
    }

    private function request($endpoint, $method = 'GET', $data = null, $headers = []) {
        if (!$this->isConfigured()) return null;

        $url = $this->url . '/rest/v1/' . ltrim($endpoint, '/');
        $ch = curl_init();

        $defaultHeaders = [
            'apikey: ' . $this->key,
            'Authorization: Bearer ' . $this->key,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];

        $finalHeaders = array_merge($defaultHeaders, $headers);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $finalHeaders);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if ($data !== null) {
            $payload = is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($res, true);
        }

        return null;
    }

    public function selectAll($table, $order = null) {
        $endpoint = $table . '?select=*';
        if ($order) {
            $endpoint .= '&order=' . $order;
        }
        return $this->request($endpoint, 'GET');
    }

    public function upsert($table, $data, $onConflict = 'id') {
        $endpoint = $table . '?on_conflict=' . $onConflict;
        $headers = ['Prefer: resolution=merge-duplicates,return=representation'];
        return $this->request($endpoint, 'POST', $data, $headers);
    }

    public function delete($table, $field, $value) {
        $endpoint = $table . '?' . $field . '=eq.' . urlencode($value);
        return $this->request($endpoint, 'DELETE');
    }
}
