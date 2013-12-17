asedit
======

A PHP script to manage App Store descriptions using iTMSTransporter. Use entirely at your own risk.

    Usage: ./asedit
        --sku SKU           SKU for app
        --user USERNAME     Apple ID username
        --pass PASSWORD     Apple ID password
        --get               Get metadata from App Store to /data directory (needs user/pass)
        --extract           Extract descriptions in metadata.xml to /text directory
        --replace           Replace descriptions in metadata.xml with those in /text directory
        --verify            Verify data (needs user/pass)
        --upload            Upload data (needs user/pass)

## Example Usage

First get the current metadata package from apple servers to `/data` directory, and extract the descriptions to the `/text` directory:

    ./asedit --sku 19 --get --extract
    
Now edit the descriptions in the `/text` directory using your favourite text editor. When done, put the descriptions back in the xml file:

    ./asedit --sku 19 --replace

Then verify the new metadata:

    ./asedit --sku 19 --verify
    
If all is good, upload the new metadata to Apple:

    ./asedit --sku 19 --upload
