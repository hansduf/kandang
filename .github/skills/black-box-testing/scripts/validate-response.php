<?php
/**
 * validate-response.php - API response schema validator
 * 
 * Validates that API responses conform to expected schemas
 * 
 * Usage in Tinker:
 *   $validator = new ResponseValidator();
 *   $validator->validate($response, 'kandang_list');
 */

class ResponseValidator
{
    private $schemas = [];

    public function __construct()
    {
        $this->registerSchemas();
    }

    /**
     * Register known API response schemas
     */
    private function registerSchemas()
    {
        // Kandang record schema
        $this->schemas['kandang'] = [
            'required' => ['id', 'nama_kandang', 'lokasi', 'kapasitas', 'pic_id'],
            'types' => [
                'id' => 'integer',
                'nama_kandang' => 'string',
                'lokasi' => 'string',
                'kapasitas' => 'integer',
                'pic_id' => 'integer',
                'created_at' => 'string',
                'updated_at' => 'string',
            ],
        ];

        // Kandang list response schema
        $this->schemas['kandang_list'] = [
            'type' => 'array',
            'itemSchema' => 'kandang',
        ];

        // Production record schema
        $this->schemas['produksi_telur'] = [
            'required' => ['id', 'kandang_id', 'tanggal_produksi', 'jumlah_butir', 'jumlah_kg'],
            'types' => [
                'id' => 'integer',
                'kandang_id' => 'integer',
                'tanggal_produksi' => 'string',
                'jumlah_butir' => 'integer',
                'jumlah_kg' => 'number',
                'hdp' => 'integer',
                'hhp' => 'integer',
                'mortalitas' => 'integer',
            ],
        ];

        // Stock record schema
        $this->schemas['stok_telur'] = [
            'required' => ['id', 'kandang_id', 'jumlah_butir', 'jumlah_kg'],
            'types' => [
                'id' => 'integer',
                'kandang_id' => 'integer',
                'jumlah_butir' => 'integer',
                'jumlah_kg' => 'number',
                'tanggal_stok' => 'string',
            ],
        ];

        // Sales transaction schema
        $this->schemas['penjualan'] = [
            'required' => ['id', 'user_id', 'pembeli', 'tanggal_penjualan', 'total'],
            'types' => [
                'id' => 'integer',
                'user_id' => 'integer',
                'pembeli' => 'string',
                'tanggal_penjualan' => 'string',
                'total' => 'number',
            ],
        ];

        // User schema (sensitive - hide password)
        $this->schemas['user'] = [
            'required' => ['id', 'name', 'email'],
            'types' => [
                'id' => 'integer',
                'name' => 'string',
                'email' => 'string',
                'roles' => 'array',
            ],
            'forbidden' => ['password', 'password_hash'],
        ];
    }

    /**
     * Validate a response against a schema
     * 
     * @param mixed $response Response data (array or JSON string)
     * @param string $schemaName Schema name to validate against
     * @return array Validation result with 'valid' and 'errors' keys
     */
    public function validate($response, $schemaName)
    {
        if (is_string($response)) {
            $response = json_decode($response, true);
        }

        if (!isset($this->schemas[$schemaName])) {
            return [
                'valid' => false,
                'errors' => ["Unknown schema: $schemaName"],
            ];
        }

        $schema = $this->schemas[$schemaName];
        $errors = [];

        // Handle array schema
        if ($schema['type'] ?? null === 'array') {
            if (!is_array($response)) {
                return [
                    'valid' => false,
                    'errors' => ['Expected array, got ' . gettype($response)],
                ];
            }

            $itemSchema = $schema['itemSchema'] ?? null;
            if ($itemSchema && isset($this->schemas[$itemSchema])) {
                foreach ($response as $idx => $item) {
                    $itemValidation = $this->validate($item, $itemSchema);
                    if (!$itemValidation['valid']) {
                        $errors[] = "Item $idx: " . implode(', ', $itemValidation['errors']);
                    }
                }
            }
        } else {
            // Validate object schema
            $errors = array_merge(
                $errors,
                $this->validateRequiredFields($response, $schema['required'] ?? []),
                $this->validateFieldTypes($response, $schema['types'] ?? []),
                $this->validateForbiddenFields($response, $schema['forbidden'] ?? [])
            );
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Check required fields are present
     */
    private function validateRequiredFields($data, $required)
    {
        $errors = [];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $errors[] = "Missing required field: $field";
            }
        }
        return $errors;
    }

    /**
     * Check field types match expected
     */
    private function validateFieldTypes($data, $types)
    {
        $errors = [];
        foreach ($types as $field => $expectedType) {
            if (!isset($data[$field])) {
                continue;
            }

            $actualType = $this->getType($data[$field]);
            if ($actualType !== $expectedType) {
                $errors[] = "Field $field: expected $expectedType, got $actualType";
            }
        }
        return $errors;
    }

    /**
     * Check sensitive fields are not present
     */
    private function validateForbiddenFields($data, $forbidden)
    {
        $errors = [];
        foreach ($forbidden as $field) {
            if (isset($data[$field])) {
                $errors[] = "Forbidden field present: $field";
            }
        }
        return $errors;
    }

    /**
     * Get PHP type, map to JSON types
     */
    private function getType($value)
    {
        if (is_null($value)) return 'null';
        if (is_bool($value)) return 'boolean';
        if (is_int($value)) return 'integer';
        if (is_float($value)) return 'number';
        if (is_string($value)) return 'string';
        if (is_array($value)) return 'array';
        return 'unknown';
    }

    /**
     * Get registered schema names
     */
    public function getAvailableSchemas()
    {
        return array_keys($this->schemas);
    }
}

// Helper function for Tinker
if (!function_exists('validateResponse')) {
    function validateResponse($response, $schema) {
        $validator = new ResponseValidator();
        $result = $validator->validate($response, $schema);
        
        echo $result['valid'] 
            ? "✅ Response is valid\n" 
            : "❌ Response validation failed:\n";
        
        foreach ($result['errors'] as $error) {
            echo "   - $error\n";
        }
        
        return $result;
    }
}

echo "✅ Response validator loaded\n";
echo "   Use: validateResponse(\$response, 'schema_name')\n";
echo "   Available schemas: " . implode(', ', (new ResponseValidator())->getAvailableSchemas()) . "\n";
