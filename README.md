# LMS WP-CLI Mirror Importer (Pro)

A specialized, enterprise-grade WordPress XML importer designed to solve the critical problem of moving MasterStudy LMS content between sites.

## 🚀 The Problem

Official methods for migrating MasterStudy LMS courses, lessons, and quizzes are often unreliable. The standard WordPress GUI importer frequently fails due to:
- **Server Timeouts:** Large LMS datasets cause the browser to hang.
- **Memory Exhaustion:** Standard PHP imports are memory-intensive.
- **Manual Monitoring:** Closing the browser tab often kills the import process.

## 💡 The Solution (Unique & Background-Driven)

The **LMS WP-CLI Mirror Importer** is a custom-crafted solution designed for 100% reliability. It utilizes the power of **WP-CLI** to run the import as a background process directly on the server.

### Why This is Unique:
- **Tab Closure Safety:** Unlike the default importer, you can start the process and safely close your browser tab. The import continues to run securely on the server until completion.
- **Server-Side Mirroring:** It mirrors the data directly through the command line, bypassing the limitations of traditional web-based imports.
- **Automated Completion:** Built-in intelligence to detect when the background process has finished successfully.
- **Background Execution:** This is a feature not present in the default WordPress importer, making this plugin a first-of-its-kind tool for MasterStudy LMS migrations.

## 🛠️ How to Use

1. **Export Content:** On your source site, navigate to `Tools` -> `Export` and choose **"All content"** (this creates the standard XML file).
2. **Upload to Mirror:** On your destination site, upload the XML file through the **LMS CLI Mirror** dashboard.
3. **Start & Relax:** Initiate the import. You are now free to close the tab or navigate away. The server handles the rest in the background.

## 📋 System Requirements

- **WP-CLI:** Must be installed on the server.
- **Shell Execution:** `shell_exec` must be enabled.
- **Dependencies:** Requires **MasterStudy LMS** and the core **WordPress Importer** plugin.

## ⚠️ Important Note

This plugin has been optimized for "All content" XML exports. Testing for split files (individual courses, lessons, or quizzes exports) is currently ongoing. For the most reliable results, please use the "All content" export option.

## License

This project is licensed under the GPL-2.0 or later license.
