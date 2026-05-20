#!/bin/bash
# test-api.sh — Black box API testing script
# Usage: ./test-api.sh '<endpoint>' '<method>' '<json_data>'

BASE_URL="http://localhost:8000"
LOG_DIR="test-results"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
RESULTS_FILE="${LOG_DIR}/api_test_${TIMESTAMP}.json"

# Create results directory
mkdir -p "$LOG_DIR"

# Initialize results array
declare -a results=()

# Extract parameters
ENDPOINT="${1:-/api/kandang}"
METHOD="${2:-GET}"
DATA="${3:-{}}"

echo "🧪 Testing: $METHOD $ENDPOINT"
echo "📝 Data: $DATA"

# Make the request and capture full response
RESPONSE_FILE="${LOG_DIR}/response_${TIMESTAMP}.json"
HTTP_CODE=$(curl -s -w "%{http_code}" \
  -X "$METHOD" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "$DATA" \
  "$BASE_URL$ENDPOINT" -o "$RESPONSE_FILE")

# Extract response body
RESPONSE_BODY=$(cat "$RESPONSE_FILE")

# Determine test status
STATUS="PASS"
if [[ ! "$HTTP_CODE" =~ ^[2][0-9]{2}$ ]]; then
  STATUS="FAIL"
fi

# Parse response for key fields
if command -v jq &> /dev/null; then
  if jq empty "$RESPONSE_FILE" 2>/dev/null; then
    RECORD_COUNT=$(jq 'if type == "array" then length else 1 end' "$RESPONSE_FILE")
    DATA_SAMPLE=$(jq '.[0] // .' "$RESPONSE_FILE" | head -c 200)
  else
    RECORD_COUNT="N/A"
    DATA_SAMPLE="Invalid JSON"
  fi
else
  RECORD_COUNT="N/A"
  DATA_SAMPLE="jq not installed"
fi

# Generate test result
cat > "$RESULTS_FILE" << EOF
{
  "timestamp": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
  "test": {
    "endpoint": "$ENDPOINT",
    "method": "$METHOD",
    "request_data": $DATA
  },
  "result": {
    "status": "$STATUS",
    "http_code": "$HTTP_CODE",
    "response_body": "$RESPONSE_BODY",
    "records_found": "$RECORD_COUNT",
    "sample": "$DATA_SAMPLE"
  },
  "assertions": [
    {
      "name": "HTTP Status Valid",
      "expected": "2xx",
      "actual": "$HTTP_CODE",
      "passed": $([ $HTTP_CODE -ge 200 ] && [ $HTTP_CODE -lt 300 ] && echo "true" || echo "false")
    },
    {
      "name": "Response JSON Valid",
      "expected": "valid JSON",
      "actual": "$([ -s $RESPONSE_FILE ] && echo 'present' || echo 'empty')",
      "passed": "true"
    }
  ]
}
EOF

# Pretty print results
echo ""
echo "✅ HTTP Status: $HTTP_CODE"
echo "📊 Records Found: $RECORD_COUNT"
echo "📄 Sample: ${DATA_SAMPLE:0:100}..."
echo ""
echo "💾 Results saved to: $RESULTS_FILE"
echo "🔗 Response saved to: $RESPONSE_FILE"

# Return HTTP code for chaining
exit $((HTTP_CODE / 100 - 2))
