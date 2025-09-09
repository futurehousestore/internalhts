# InternalHTS - Dolibarr Module

A comprehensive Dolibarr module for generating internal invoices with HS/HTS codes, Country of Origin (COO), customs values, weights, and packages. This module is designed for businesses that need to track harmonized tariff schedule codes and customs information for their products and shipments.

## Features

### Core Functionality
- **Internal HTS Invoices**: Create and manage internal invoices with customs information
- **HTS Code Management**: Master table of Harmonized Tariff Schedule codes with duty rates
- **Product Mapping**: Map products to specific HTS codes with country of origin and customs values
- **Shipment Integration**: Auto-generate invoices from existing Dolibarr shipments

### Data Management
- **Weight Tracking**: Track weight in kilograms for customs purposes
- **Package Counting**: Count packages for shipping and customs documentation
- **Customs Values**: Separate customs values from standard pricing
- **Country of Origin**: Track manufacturing country for trade compliance

### Export & Integration
- **PDF Generation**: Commercial invoices and packing lists for customs
- **Broker Exports**: CSV and JSON formats for customs brokers
- **API Endpoints**: RESTful API for external system integration
- **Batch Import**: Import HTS codes from CSV files

### User Interface
- **Dark Mode Support**: Compatible with modern UI themes
- **Responsive Design**: Mobile-friendly interface
- **Multi-language**: Extensible language system
- **Permission-based Access**: Fine-grained user permissions

## Installation

1. **Download**: Extract the module files to your Dolibarr custom modules directory:
   ```
   /dolibarr/htdocs/custom/internalhts/
   ```

2. **Activate**: Go to Home → Setup → Modules and activate the "InternalHTS" module

3. **Configure**: Navigate to the module setup page to configure:
   - Invoice numbering format
   - Default PDF templates
   - Default countries of origin
   - Import HTS codes from CSV

4. **Permissions**: Set user permissions for:
   - Read internal HTS invoices
   - Create/modify internal HTS invoices  
   - Delete internal HTS invoices

## Usage

### Setting Up HTS Codes

1. Go to the module setup page
2. Use the HTS import feature to upload a CSV file with columns:
   - Code (e.g., "8471.30.01")
   - Label (e.g., "Laptop computers")
   - Description (optional)
   - Duty Rate (decimal, e.g., 0.0825 for 8.25%)

### Creating Product Mappings

1. Navigate to HTS Mapping from the module menu
2. Create new mappings linking products to HTS codes
3. Set country of origin and customs values
4. Configure weight overrides if needed

### Generating Invoices

**Manual Creation:**
1. Go to InternalHTS → New Internal HTS Invoice
2. Select customer and shipment (optional)
3. Add products with HTS information
4. Validate and generate documents

**Auto-generation from Shipments:**
1. Open an existing shipment
2. Use "Generate Internal HTS Invoice" action
3. System automatically creates invoice with mapped HTS data

### Exporting Data

- **PDF Documents**: Generate commercial invoices and packing lists
- **Broker CSV**: Export customs data for freight forwarders
- **Broker JSON**: Structured data for API integration

## Database Schema

### Core Tables

- `llx_c_hts_codes`: Master table of HTS codes and duty rates
- `llx_internalhts_hts_mapping`: Product-to-HTS code mappings
- `llx_internalhts_invoice`: Invoice headers with customs information
- `llx_internalhts_invoice_line`: Invoice line items with HTS details

### Key Fields

- **HTS Code**: 10-digit harmonized tariff schedule code
- **Country of Origin**: 2-letter ISO country code
- **Customs Value**: Value for customs declaration purposes
- **Weight**: Product weight in kilograms
- **Packages**: Number of packages/pieces

## API Endpoints

The module provides RESTful API endpoints for external integration:

- `GET /api/internalhts/invoices` - List invoices
- `POST /api/internalhts/invoices` - Create invoice
- `GET /api/internalhts/invoices/{id}` - Get invoice details
- `PUT /api/internalhts/invoices/{id}` - Update invoice
- `GET /api/internalhts/mappings` - List HTS mappings
- `POST /api/internalhts/mappings` - Create mapping

## Technical Requirements

- **Dolibarr**: Version 11.0 or higher
- **PHP**: Version 7.0 or higher
- **Database**: MySQL/MariaDB with InnoDB engine
- **Permissions**: Write access to Dolibarr custom modules directory

## File Structure

```
internalhts/
├── core/modules/
│   ├── modInternalHTS.class.php          # Module descriptor
│   └── internalhts/
│       ├── mod_internalhts_standard.php  # Numbering module
│       └── modules_internalhts.php       # Base numbering class
├── class/
│   ├── internalhts_invoice.class.php     # Invoice business logic
│   ├── internalhts_invoice_line.class.php # Invoice line logic
│   └── hts_mapping.class.php             # HTS mapping logic
├── admin/
│   ├── setup.php                         # Configuration page
│   └── about.php                         # About page
├── sql/
│   └── llx_internalhts.sql              # Database schema
├── langs/en_US/
│   └── internalhts.lang                  # English translations
├── lib/
│   └── internalhts.lib.php              # Utility functions
├── css/
│   └── internalhts.css.php              # Stylesheets
├── internalhtsindex.php                  # Module homepage
├── internalhts_invoice_list.php          # Invoice list page
└── README.md                             # Documentation
```

## Support & Development

This module is developed and maintained by FutureHouse Store. For support, bug reports, or feature requests, please contact the development team.

### License

This module is released under the GNU General Public License v3.0 or later (GPLv3+). See the LICENSE file for full details.

### Contributing

Contributions are welcome! Please follow Dolibarr coding standards and submit pull requests for review.