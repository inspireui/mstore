WebP Express 0.15.3. Conversion triggered using bulk conversion, 2019-09-24 04:22:59

*WebP Convert 2.1.4*  ignited.
- PHP version: 7.3.1
- Server software: Apache

Stack converter ignited

Options:
------------
The following options have been set explicitly. Note: it is the resulting options after merging down the "jpeg" and "png" options and any converter-prefixed options.
- source: [doc-root]/wp-content/uploads/2015/07/image1xxl.jpg
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2015/07/image1xxl.jpg.webp
- log-call-arguments: true
- converters: (array of 9 items)

The following options have not been explicitly set, so using the following defaults:
- converter-options: (empty array)
- shuffle: false
- preferred-converters: (empty array)
- extra-converters: (empty array)

The following options were supplied and are passed on to the converters in the stack:
- default-quality: 70
- encoding: "auto"
- max-quality: 80
- metadata: "none"
- near-lossless: 60
- quality: "auto"
------------


*Trying: cwebp* 

*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


Options:
------------
The following options have been set explicitly. Note: it is the resulting options after merging down the "jpeg" and "png" options and any converter-prefixed options.
- source: [doc-root]/wp-content/uploads/2015/07/image1xxl.jpg
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2015/07/image1xxl.jpg.webp
- default-quality: 70
- encoding: "auto"
- low-memory: true
- log-call-arguments: true
- max-quality: 80
- metadata: "none"
- method: 6
- near-lossless: 60
- quality: "auto"
- use-nice: true
- command-line-options: ""
- try-common-system-paths: true
- try-supplied-binary-for-os: true

The following options have not been explicitly set, so using the following defaults:
- alpha-quality: 85
- auto-filter: false
- preset: "none"
- size-in-percentage: null (not set)
- skip: false
- rel-path-to-precompiled-binaries: *****
------------

Encoding is set to auto - converting to both lossless and lossy and selecting the smallest file

Converting to lossy
Locating cwebp binaries
1 cwebp binaries found in common system locations
Checking if we have a supplied binary for OS: Darwin... We do.
We in fact have 1
A total of 2 cwebp binaries where found
Detecting versions of the cwebp binaries found (and verifying that they can be executed in the process)
Executing: /usr/local/bin/cwebp -version. Result: version: 1.0.3
Executing: [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/webp-convert/src/Convert/Converters/Binaries/cwebp-mac12 -version
Exec failed (the cwebp binary was not found at path: [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/webp-convert/src/Convert/Converters/Binaries/cwebp-mac12)
Trying executing the cwebs found until success. Starting with the ones with highest version number.
Creating command line options for version: 1.0.3

*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 

Quality of source could not be established (Imagick or GraphicsMagick is required) - Using default instead (70).
The near-lossless option ignored for lossy
Trying to convert by executing the following command:
nice /usr/local/bin/cwebp -metadata none -q 70 -alpha_q '85' -m 6 -low_memory '[doc-root]/wp-content/uploads/2015/07/image1xxl.jpg' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2015/07/image1xxl.jpg.webp.lossy.webp' 2>&1

*Output:* 
Could not read 0 bytes of data from file [doc-root]/wp-content/uploads/2015/07/image1xxl.jpg
Error! Could not process file [doc-root]/wp-content/uploads/2015/07/image1xxl.jpg
Error! Cannot read input picture file '[doc-root]/wp-content/uploads/2015/07/image1xxl.jpg'

Exec failed (return code: 255)

**Error: ** **Failed converting. Check the conversion log for details.** 
Failed converting. Check the conversion log for details.
cwebp failed in 123 ms

*Trying: vips* 

*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


**Error: ** **Required Vips extension is not available.** 
Required Vips extension is not available.
vips failed in 2 ms

*Trying: imagemagick* 

*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


**Error: ** **imagemagick is not installed (cannot execute: "convert")** 
imagemagick is not installed (cannot execute: "convert")
imagemagick failed in 5 ms

*Trying: graphicsmagick* 

*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


**Error: ** **gmagick is not installed** 
gmagick is not installed
graphicsmagick failed in 5 ms

*Trying: wpc* 

*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


**Error: ** **Missing URL. You must install Webp Convert Cloud Service on a server, or the WebP Express plugin for Wordpress - and supply the url.** 
Missing URL. You must install Webp Convert Cloud Service on a server, or the WebP Express plugin for Wordpress - and supply the url.
wpc failed in 9 ms

*Trying: ewww* 

*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


**Error: ** **Missing API key.** 
Missing API key.
ewww failed in 3 ms

*Trying: imagick* 

*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


**Error: ** **iMagick was compiled without WebP support.** 
iMagick was compiled without WebP support.
imagick failed in 2 ms

*Trying: gmagick* 

*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


**Error: ** **Required Gmagick extension is not available.** 
Required Gmagick extension is not available.
gmagick failed in 3 ms

*Trying: gd* 

*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


*Notice: exif_imagetype(): Read error! in [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/image-mime-type-guesser/src/Detectors/ExifImageType.php, line 29, PHP 7.3.1 (Darwin)* 


**Error: ** **Gd has been compiled without webp support.** 
Gd has been compiled without webp support.
gd failed in 2 ms

Stack failed in 154 ms

**Error: ** **None of the converters in the stack are operational** 
