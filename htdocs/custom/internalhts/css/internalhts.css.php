<?php
// CSS for InternalHTS module

header('Content-type: text/css');
?>

/* InternalHTS module styles */
.internalhts-draft {
    color: #666;
}

.internalhts-validated {
    color: #0a7e07;
    font-weight: bold;
}

.internalhts-line-total {
    text-align: right;
    font-weight: bold;
}

.internalhts-customs-value {
    color: #0066cc;
}

.internalhts-hts-code {
    font-family: monospace;
    background-color: #f5f5f5;
    padding: 2px 4px;
    border-radius: 3px;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .internalhts-hts-code {
        background-color: #2d2d2d;
        color: #e0e0e0;
    }
    
    .internalhts-draft {
        color: #999;
    }
    
    .internalhts-validated {
        color: #4ade80;
    }
    
    .internalhts-customs-value {
        color: #60a5fa;
    }
}

/* Manual dark mode class support */
body.dark .internalhts-hts-code {
    background-color: #2d2d2d;
    color: #e0e0e0;
}

body.dark .internalhts-draft {
    color: #999;
}

body.dark .internalhts-validated {
    color: #4ade80;
}

body.dark .internalhts-customs-value {
    color: #60a5fa;
}

/* Table styling for lists */
.internalhts-table {
    width: 100%;
}

.internalhts-table th {
    background-color: #f8f9fa;
    padding: 8px;
    text-align: left;
}

.internalhts-table td {
    padding: 8px;
    border-bottom: 1px solid #dee2e6;
}

/* Dark mode table styling */
@media (prefers-color-scheme: dark) {
    .internalhts-table th {
        background-color: #2d3748;
        color: #e2e8f0;
    }
    
    .internalhts-table td {
        border-bottom-color: #4a5568;
    }
}

body.dark .internalhts-table th {
    background-color: #2d3748;
    color: #e2e8f0;
}

body.dark .internalhts-table td {
    border-bottom-color: #4a5568;
}

/* Form styling */
.internalhts-form-row {
    margin-bottom: 10px;
}

.internalhts-form-label {
    display: inline-block;
    width: 150px;
    font-weight: bold;
}

.internalhts-form-input {
    width: 300px;
}

/* HTS import styling */
.hts-import-results {
    margin-top: 20px;
}

.hts-import-success {
    color: #155724;
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 10px;
}

.hts-import-error {
    color: #721c24;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 10px;
}

/* Dark mode form and import styling */
@media (prefers-color-scheme: dark) {
    .hts-import-success {
        color: #d4edda;
        background-color: #155724;
        border-color: #0f2419;
    }
    
    .hts-import-error {
        color: #f8d7da;
        background-color: #721c24;
        border-color: #501014;
    }
}

body.dark .hts-import-success {
    color: #d4edda;
    background-color: #155724;
    border-color: #0f2419;
}

body.dark .hts-import-error {
    color: #f8d7da;
    background-color: #721c24;
    border-color: #501014;
}