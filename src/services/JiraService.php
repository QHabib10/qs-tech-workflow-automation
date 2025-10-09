<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class JiraService {
    private $client;
    private $conn;
    private $baseUrl;
    private $auth;

    public function __construct($conn = null) {
        $this->conn = $conn;
        $this->baseUrl = getenv('JIRA_URL');
        $this->auth = [getenv('JIRA_EMAIL'), getenv('JIRA_API_TOKEN')];
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'auth' => $this->auth,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    public function createIssue($leadData) {
        try {
            $issueData = $this->buildIssueData($leadData);
            
            $response = $this->client->post('/rest/api/3/issue', [
                'json' => $issueData
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            $this->logJiraActivity('jira_issue_created', $result['key'], $leadData['id']);
            
            return $result;
            
        } catch (Exception $e) {
            $this->logJiraActivity('jira_issue_failed', null, $leadData['id'], $e->getMessage());
            return false;
        }
    }

    public function getIssue($issueKey) {
        try {
            $response = $this->client->get("/rest/api/3/issue/{$issueKey}");
            return json_decode($response->getBody(), true);
        } catch (Exception $e) {
            return false;
        }
    }

    public function updateIssue($issueKey, $updateData) {
        try {
            $this->client->put("/rest/api/3/issue/{$issueKey}", [
                'json' => $updateData
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function buildIssueData($leadData) {
        $summary = "Lead from {$leadData['name']}";
        
        // Build description in Atlassian Document Format (ADF)
        $description = [
            'type' => 'doc',
            'version' => 1,
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Lead Details:'
                        ]
                    ]
                ],
                [
                    'type' => 'bulletList',
                    'content' => [
                        [
                            'type' => 'listItem',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        [
                                            'type' => 'text',
                                            'text' => "Name: {$leadData['name']}"
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'type' => 'listItem',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        [
                                            'type' => 'text',
                                            'text' => "Email: {$leadData['email']}"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        // Add budget if available
        if (!empty($leadData['budget'])) {
            $description['content'][] = [
                'type' => 'bulletList',
                'content' => [
                    [
                        'type' => 'listItem',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'text' => "Budget: $" . number_format($leadData['budget'], 2)
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }
    
        $appUrl = getenv('APP_URL') ?: 'http://localhost:8000';
        $description['content'][] = [
            'type' => 'paragraph',
            'content' => [
                [
                    'type' => 'text',
                    'text' => "Link back to app: {$appUrl}/src/pages/lead_capture.php"
                ]
            ]
        ];

        return [
            'fields' => [
                'project' => ['key' => getenv('JIRA_PROJECT_KEY')],
                'summary' => $summary,
                'description' => $description,
                'issuetype' => ['name' => getenv('JIRA_ISSUE_TYPE') ?: 'Task'],
                'labels' => ['lead', 'auto-generated']
            ]
        ];
    }
    
    private function logJiraActivity($action, $issueKey = null, $leadId = null, $error = null) {
        if ($this->conn) {
            $details = json_encode([
                'issue_key' => $issueKey,
                'status' => $error ? 'failed' : 'success',
                'error' => $error
            ]);
            
            $stmt = $this->conn->prepare("INSERT INTO audit_log (action, entity, entity_id, details) VALUES (?, 'leads', ?, ?)");
            $stmt->bind_param('sis', $action, $leadId, $details);
            $stmt->execute();
            $stmt->close();
        } else {
            error_log("JiraService: Database connection not available. Action: {$action}, Issue: {$issueKey}, Lead: {$leadId}, Error: {$error}");
        }
    }
}
