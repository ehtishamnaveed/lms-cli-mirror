# LMS WP-CLI Mirror Importer (Pro)

A high-performance, enterprise-grade WordPress XML importer designed for professional MasterStudy LMS environments. This plugin utilizes the power of WP-CLI to provide real-time monitoring and robust error handling for large-scale migrations.

## Key Features

- **Enterprise Importer Engine:** A production-grade importer for handling high-volume XML data.
- **WP-CLI Powered:** Leverages the command line for maximum performance and stability, bypassing standard PHP time and memory limits.
- **Real-Time Monitoring:** Track the import progress with a dedicated monitoring dashboard.
- **System Diagnostics:** Automatic environment checking for required system dependencies (Shell Exec, WP-CLI, MasterStudy LMS, WP Importer).
- **Automated Optimizations:** Dynamically adjusts server configurations (PHP memory, execution time, and file size limits) to ensure success.
- **Automated Completion Detection:** Intelligent monitoring of the import process to identify success or failure.

## Prerequisites

To ensure peak performance and stability, this plugin requires:
- **WP-CLI:** Access to the WordPress command-line interface.
- **Shell Execution:** Enabled `shell_exec` in PHP configuration.
- **MasterStudy LMS:** Either the standard or Pro version must be installed and active.
- **WordPress Importer:** The core WordPress importer plugin.

## Installation

1. Download the `lms-cli-mirror.zip` from the [Releases](https://github.com/omagucchy/lms-cli-mirror/releases) page.
2. Log in to your WordPress admin dashboard.
3. Navigate to `Plugins` -> `Add New` -> `Upload Plugin`.
4. Select the downloaded ZIP file and click `Install Now`.
5. Activate the plugin.

## Usage

1. Navigate to the `LMS CLI Mirror` tab in your WordPress admin menu.
2. Review the **System Diagnostics** to ensure your server environment is ready.
3. Drag and drop your XML file into the upload area or click to browse.
4. Once the upload is complete, follow the on-screen instructions to initiate the WP-CLI import.

## Security

This plugin is designed for enterprise environments. It implements security best practices to prevent unauthorized access while providing necessary system-level access for large-scale data processing.

## Versioning

Current Version: **4.9.0 AUTO**

## License

This project is licensed under the GPL-2.0 or later license.
