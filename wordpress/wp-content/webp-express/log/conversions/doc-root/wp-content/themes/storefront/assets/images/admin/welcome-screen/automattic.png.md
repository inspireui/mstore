WebP Express 0.15.3. Conversion triggered using bulk conversion, 2019-09-24 06:01:19

*WebP Convert 2.1.4*  ignited.
- PHP version: 7.3.1
- Server software: Apache

Stack converter ignited

Options:
------------
The following options have been set explicitly. Note: it is the resulting options after merging down the "jpeg" and "png" options and any converter-prefixed options.
- source: [doc-root]/wp-content/themes/storefront/assets/images/admin/welcome-screen/automattic.png
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/themes/storefront/assets/images/admin/welcome-screen/automattic.png.webp
- log-call-arguments: true
- converters: (array of 9 items)

The following options have not been explicitly set, so using the following defaults:
- converter-options: (empty array)
- shuffle: false
- preferred-converters: (empty array)
- extra-converters: (empty array)

The following options were supplied and are passed on to the converters in the stack:
- alpha-quality: 80
- encoding: "auto"
- metadata: "none"
- near-lossless: 60
- quality: 85
------------


*Trying: cwebp* 

Options:
------------
The following options have been set explicitly. Note: it is the resulting options after merging down the "jpeg" and "png" options and any converter-prefixed options.
- source: [doc-root]/wp-content/themes/storefront/assets/images/admin/welcome-screen/automattic.png
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/themes/storefront/assets/images/admin/welcome-screen/automattic.png.webp
- alpha-quality: 80
- encoding: "auto"
- low-memory: true
- log-call-arguments: true
- metadata: "none"
- method: 6
- near-lossless: 60
- quality: 85
- use-nice: true
- command-line-options: ""
- try-common-system-paths: true
- try-supplied-binary-for-os: true

The following options have not been explicitly set, so using the following defaults:
- auto-filter: false
- default-quality: 85
- max-quality: 85
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
Quality: 85. 
The near-lossless option ignored for lossy
Trying to convert by executing the following command:
nice /usr/local/bin/cwebp -metadata none -q 85 -alpha_q '80' -m 6 -low_memory '[doc-root]/wp-content/themes/storefront/assets/images/admin/welcome-screen/automattic.png' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/themes/storefront/assets/images/admin/welcome-screen/automattic.png.webp.lossy.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/themes/storefront/assets/images/admin/welcome-screen/automattic.png.webp.lossy.webp'
File:      [doc-root]/wp-content/themes/storefront/assets/images/admin/welcome-screen/automattic.png
Dimension: 660 x 52 (with alpha)
Output:    7456 bytes Y-U-V-All-PSNR 46.65 50.01 53.12   47.72 dB
           (1.74 bpp)
block count:  intra4:         95  (56.55%)
              intra16:        73  (43.45%)
              skipped:        37  (22.02%)
bytes used:  header:            287  (3.8%)
             mode-partition:    500  (6.7%)
             transparency:     2367 (66.2 dB)
 Residuals bytes  |segment 1|segment 2|segment 3|segment 4|  total
  intra4-coeffs:  |    3584 |       0 |       0 |       0 |    3584  (48.1%)
 intra16-coeffs:  |     134 |       0 |       0 |       3 |     137  (1.8%)
  chroma coeffs:  |     519 |       0 |       0 |       4 |     523  (7.0%)
    macroblocks:  |      90%|       1%|       0%|       9%|     168
      quantizer:  |      15 |       9 |       8 |       8 |
   filter level:  |       4 |       2 |       2 |       0 |
------------------+---------+---------+---------+---------+-----------------
 segments total:  |    4237 |       0 |       0 |       7 |    4244  (56.9%)
Lossless-alpha compressed size: 2366 bytes
  * Header size: 81 bytes, image data size: 2285
  * Precision Bits: histogram=3 transform=3 cache=0
  * Palette size:   91

Success
Reduction: 33% (went from 11 kb to 7 kb)

Converting to lossless
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
Trying to convert by executing the following command:
nice /usr/local/bin/cwebp -metadata none -q 85 -alpha_q '80' -near_lossless 60 -m 6 -low_memory '[doc-root]/wp-content/themes/storefront/assets/images/admin/welcome-screen/automattic.png' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/themes/storefront/assets/images/admin/welcome-screen/automattic.png.webp.lossless.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/themes/storefront/assets/images/admin/welcome-screen/automattic.png.webp.lossless.webp'
File:      [doc-root]/wp-content/themes/storefront/assets/images/admin/welcome-screen/automattic.png
Dimension: 660 x 52
Output:    4102 bytes (0.96 bpp)
Lossless-ARGB compressed size: 4102 bytes
  * Header size: 221 bytes, image data size: 3855
  * Lossless features used: SUBTRACT-GREEN
  * Precision Bits: histogram=2 transform=2 cache=9

Success
Reduction: 63% (went from 11 kb to 4 kb)

Picking lossless
cwebp succeeded :)

Converted image in 143 ms, reducing file size with 63% (went from 11 kb to 4 kb)
