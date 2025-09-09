# InternalHTS Module for Dolibarr

A Dolibarr module for managing internal HTS (Harmonized Tariff Schedule) codes and generating commercial invoices for international shipping.

## Features

- **HTS Code Management**: Import and manage HTS codes by country
- **Commercial Invoices**: Create internal commercial invoices with automatic calculations
- **PDF Generation**: Generate PDF commercial invoices for international shipping
- **CSV Import**: Bulk import HTS codes from CSV files with dry-run capability
- **Multi-country Support**: Support for different HTS codes by country
- **Auto-calculation**: Automatic calculation of line totals and customs values
- **Status Management**: Draft/Validated status workflow with proper permissions
- **Dark Mode**: Full dark mode support for better user experience
- **API Support**: REST API endpoints for integration

## Installation

1. Copy the module to your Dolibarr custom modules directory:
   ```
   htdocs/custom/internalhts/
   ```

2. Enable the module in Dolibarr:
   - Go to Home → Setup → Modules
   - Find "InternalHTS" in the list
   - Click "Activate"

3. Configure the module:
   - Go to the InternalHTS module configuration
   - Set up numbering preferences
   - Configure default values for incoterm, value source, and apportionment mode

## Usage

### Creating Commercial Invoices

1. Navigate to InternalHTS → Internal Invoices
2. Click "New Internal Invoice"
3. Fill in the header information
4. Add lines with:
   - Product description and SKU
   - HTS code (if available)
   - Country of origin
   - Quantities and prices
   - Customs values

### Managing HTS Codes

1. Navigate to InternalHTS → HTS Mapping
2. Import HTS codes from CSV:
   - Upload a CSV file with format: `country,code,description`
   - Use dry-run to validate before committing
   - Example: `USA,1234.56.78,Electronic components`

### Generating PDFs

1. Open a validated commercial invoice
2. Click "Generate PDF"
3. The system will create a commercial invoice PDF with all line details

## API Usage

The module provides REST API endpoints:

- `GET /api/internalhts/docs` - List all documents
- `POST /api/internalhts/docs` - Create a new document
- `GET /api/internalhts/docs/{id}` - Get specific document
- `PUT /api/internalhts/docs/{id}` - Update document
- `DELETE /api/internalhts/docs/{id}` - Delete document

## Configuration

### Numbering

The module uses the format `IH-YYYY-NNNNN` by default:
- IH: Prefix for Internal HTS
- YYYY: Current year
- NNNNN: Sequential number (5 digits)

### Default Values

Configure default values in the module setup:
- **Incoterm**: Default incoterm for shipments (e.g., EXW, FOB)
- **Value Source**: How to determine customs values (retail, wholesale, customs)
- **Apportionment Mode**: How to distribute costs (by weight, value, quantity)

## Permissions

The module defines three permission levels:
- **Read**: View commercial invoices and HTS codes
- **Write**: Create and modify draft invoices, import HTS codes
- **Delete**: Delete invoices (only drafts by default)

## Testing

Run the unit tests to verify functionality:
```
htdocs/custom/internalhts/test/test_numbering.php
```

Tests cover:
- Numbering module functionality
- Line calculation accuracy
- HTS import validation

## Technical Details

### Database Tables

- `llx_internalhts_hts`: HTS codes by country
- `llx_internalhts_productmap`: Product to HTS mapping
- `llx_internalhts_doc`: Document headers
- `llx_internalhts_docline`: Document lines

### File Structure

```
htdocs/custom/internalhts/
├── admin/              # Admin configuration pages
├── api/               # REST API classes
├── class/             # Business object classes
├── core/              # Module core (descriptors, numbering, PDF)
├── css/               # Stylesheets with dark mode support
├── langs/             # Translation files
├── lib/               # Library functions
├── sql/               # Database schema and migrations
├── test/              # Unit tests
├── card.php           # Document create/edit page
├── list.php           # Document list page
└── internalhtsindex.php # Module homepage
```

## Requirements

- Dolibarr 11.0+
- PHP 7.0+
- MySQL/MariaDB

## License

GPL v3+

## Support

For issues and feature requests, please use the project's issue tracker.