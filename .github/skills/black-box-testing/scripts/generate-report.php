<?php
/**
 * generate-report.php - Black box test report generator
 * Converts test results JSON to Markdown, HTML, or JSON formats
 * 
 * Usage:
 *   php generate-report.php --format=markdown --input=test-results.json --output=report.md
 *   php generate-report.php --format=html --input=test-results.json --output=report.html
 *   php generate-report.php --format=json --input=test-results.json --output=report.json
 */

class TestReportGenerator
{
    private $format;
    private $input;
    private $output;
    private $tests = [];
    private $summary = [
        'total' => 0,
        'passed' => 0,
        'failed' => 0,
        'duration_ms' => 0,
    ];

    public function __construct($format, $input, $output)
    {
        $this->format = $format;
        $this->input = $input;
        $this->output = $output;
    }

    public function run()
    {
        $this->loadResults();
        $this->calculateSummary();
        
        $content = match($this->format) {
            'markdown' => $this->generateMarkdown(),
            'html' => $this->generateHtml(),
            'json' => $this->generateJson(),
            default => throw new Exception("Unknown format: {$this->format}"),
        };

        file_put_contents($this->output, $content);
        echo "✅ Report generated: {$this->output}\n";
    }

    private function loadResults()
    {
        if (!file_exists($this->input)) {
            throw new Exception("Input file not found: {$this->input}");
        }

        $content = file_get_contents($this->input);
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON: " . json_last_error_msg());
        }

        // Handle both single test and array of tests
        $this->tests = is_array($decoded) && isset($decoded[0]) 
            ? $decoded 
            : [$decoded];
    }

    private function calculateSummary()
    {
        foreach ($this->tests as $test) {
            $this->summary['total']++;
            
            if ($test['result']['status'] === 'PASS') {
                $this->summary['passed']++;
            } else {
                $this->summary['failed']++;
            }
        }
    }

    private function generateMarkdown(): string
    {
        $md = "# Black Box Test Report\n\n";
        $md .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";

        // Summary
        $md .= "## Summary\n\n";
        $md .= "| Metric | Count |\n";
        $md .= "|--------|-------|\n";
        $md .= "| Total Tests | {$this->summary['total']} |\n";
        $md .= "| ✅ Passed | {$this->summary['passed']} |\n";
        $md .= "| ❌ Failed | {$this->summary['failed']} |\n";
        $passRate = $this->summary['total'] > 0 
            ? round(($this->summary['passed'] / $this->summary['total']) * 100, 1)
            : 0;
        $md .= "| Pass Rate | {$passRate}% |\n\n";

        // Details
        $md .= "## Test Results\n\n";

        foreach ($this->tests as $idx => $test) {
            $status = $test['result']['status'] === 'PASS' ? '✅ PASS' : '❌ FAIL';
            $endpoint = $test['test']['endpoint'] ?? 'N/A';
            $method = $test['test']['method'] ?? 'N/A';
            $httpCode = $test['result']['http_code'] ?? 'N/A';

            $md .= "### Test " . ($idx + 1) . ": $status\n\n";
            $md .= "**Endpoint:** `$method $endpoint`\n\n";
            $md .= "**HTTP Status:** $httpCode\n\n";

            if (isset($test['result']['sample'])) {
                $md .= "**Response Sample:**\n```json\n";
                $md .= substr($test['result']['sample'], 0, 500);
                $md .= "\n```\n\n";
            }

            if (isset($test['assertions']) && is_array($test['assertions'])) {
                $md .= "**Assertions:**\n\n";
                foreach ($test['assertions'] as $assertion) {
                    $check = ($assertion['passed'] ?? false) ? '✓' : '✗';
                    $md .= "- [$check] {$assertion['name']}: ";
                    $md .= "expected `{$assertion['expected']}`, got `{$assertion['actual']}`\n";
                }
                $md .= "\n";
            }
        }

        // Conclusions
        $md .= "## Conclusion\n\n";
        $md .= ($this->summary['failed'] === 0) 
            ? "All tests passed! ✅"
            : "{$this->summary['failed']} test(s) failed. Please review above. ⚠️";

        return $md;
    }

    private function generateHtml(): string
    {
        $passRate = $this->summary['total'] > 0 
            ? round(($this->summary['passed'] / $this->summary['total']) * 100, 1)
            : 0;
        $statusColor = $this->summary['failed'] === 0 ? 'green' : 'red';

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Black Box Test Report</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 20px; }
        .header { background: #f5f5f5; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .summary-card { background: white; border: 1px solid #ddd; padding: 15px; border-radius: 8px; text-align: center; }
        .summary-card h3 { margin: 0; color: #666; font-size: 14px; }
        .summary-card .number { font-size: 32px; font-weight: bold; margin: 10px 0; }
        .status-pass { color: #28a745; }
        .status-fail { color: #dc3545; }
        .tests { margin-top: 20px; }
        .test-item { background: white; border: 1px solid #ddd; margin-bottom: 15px; border-radius: 8px; overflow: hidden; }
        .test-header { background: #f8f9fa; padding: 15px; border-bottom: 1px solid #ddd; }
        .test-body { padding: 15px; }
        .assertion-list { margin-top: 10px; }
        .assertion { padding: 5px; margin: 5px 0; }
        .assertion.pass { color: #28a745; }
        .assertion.fail { color: #dc3545; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Black Box Test Report</h1>
        <p>Generated: <strong>HTML
echo date('Y-m-d H:i:s');
echo <<<HTML
</strong></p>
    </div>

    <div class="summary">
        <div class="summary-card">
            <h3>Total Tests</h3>
            <div class="number">{$this->summary['total']}</div>
        </div>
        <div class="summary-card">
            <h3>Passed</h3>
            <div class="number status-pass">{$this->summary['passed']}</div>
        </div>
        <div class="summary-card">
            <h3>Failed</h3>
            <div class="number status-fail">{$this->summary['failed']}</div>
        </div>
        <div class="summary-card">
            <h3>Pass Rate</h3>
            <div class="number" style="color: {$statusColor};">{$passRate}%</div>
        </div>
    </div>

    <div class="tests">
        <h2>Test Details</h2>
HTML;

        foreach ($this->tests as $idx => $test) {
            $status = $test['result']['status'] === 'PASS' ? 'PASS' : 'FAIL';
            $statusClass = strtolower($status);
            $endpoint = htmlspecialchars($test['test']['endpoint'] ?? 'N/A');
            $method = htmlspecialchars($test['test']['method'] ?? 'N/A');
            $httpCode = htmlspecialchars($test['result']['http_code'] ?? 'N/A');
            $sample = htmlspecialchars(substr($test['result']['sample'] ?? '', 0, 300));

            $html .= <<<HTML
        <div class="test-item">
            <div class="test-header">
                <strong>Test #$idx: <span class="status-$statusClass">$status</span></strong>
                <div style="margin-top: 5px; font-size: 13px; color: #666;">
                    $method $endpoint | HTTP $httpCode
                </div>
            </div>
            <div class="test-body">
                <div><strong>Response:</strong></div>
                <code>$sample...</code>
HTML;

            if (isset($test['assertions']) && is_array($test['assertions'])) {
                $html .= '<div class="assertion-list"><strong>Assertions:</strong>';
                foreach ($test['assertions'] as $assertion) {
                    $passed = $assertion['passed'] ?? false;
                    $mark = $passed ? '✓' : '✗';
                    $class = $passed ? 'pass' : 'fail';
                    $name = htmlspecialchars($assertion['name']);
                    $html .= "<div class=\"assertion $class\">$mark $name</div>";
                }
                $html .= '</div>';
            }

            $html .= <<<HTML
            </div>
        </div>
HTML;
        }

        $html .= <<<HTML
    </div>
</body>
</html>
HTML;

        return $html;
    }

    private function generateJson(): string
    {
        $output = [
            'timestamp' => date('Y-m-dT H:i:sZ'),
            'summary' => $this->summary,
            'tests' => $this->tests,
        ];

        return json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

// Parse command line arguments
$options = getopt('', ['format:', 'input:', 'output:']);

if (!isset($options['format']) || !isset($options['input']) || !isset($options['output'])) {
    echo "Usage: php generate-report.php --format=<format> --input=<file> --output=<file>\n";
    echo "Formats: markdown, html, json\n";
    exit(1);
}

try {
    $generator = new TestReportGenerator(
        $options['format'],
        $options['input'],
        $options['output']
    );
    $generator->run();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
