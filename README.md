asedit
======

Quick script to manage App Store descriptions using iTMSTransporter. It works for me. Use at your own risk!

    Usage: ./asedit
        --sku SKU           SKU for app
        --user USERNAME     Apple ID username
        --pass PASSWORD     Apple ID password
        --get               Get metadata from App Store to /data directory (needs user/pass)
        --extract           Extract descriptions in metadata.xml to 'text' directory
        --replace           Replace descriptions in metadata.xml with those in 'text' directory
        --verify            Verify data (needs user/pass)
        --upload            Upload data (needs user/pass)


