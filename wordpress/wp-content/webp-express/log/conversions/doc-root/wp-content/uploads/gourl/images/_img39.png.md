WebP Express 0.15.3. Conversion triggered using bulk conversion, 2019-09-24 06:00:05

*WebP Convert 2.1.4*  ignited.
- PHP version: 7.3.1
- Server software: Apache

Stack converter ignited

Options:
------------
The following options have been set explicitly. Note: it is the resulting options after merging down the "jpeg" and "png" options and any converter-prefixed options.
- source: [doc-root]/wp-content/uploads/gourl/images/_img39.png
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img39.png.webp
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
- source: [doc-root]/wp-content/uploads/gourl/images/_img39.png
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img39.png.webp
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
nice /usr/local/bin/cwebp -metadata none -q 85 -alpha_q '80' -m 6 -low_memory '[doc-root]/wp-content/uploads/gourl/images/_img39.png' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img39.png.webp.lossy.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img39.png.webp.lossy.webp'
File:      [doc-root]/wp-content/uploads/gourl/images/_img39.png
Dimension: 256 x 256 (with alpha)
Output:    4686 bytes Y-U-V-All-PSNR 46.05 50.17 51.54   47.14 dB
           (0.57 bpp)
block count:  intra4:        132  (51.56%)
              intra16:       124  (48.44%)
              skipped:        15  (5.86%)
bytes used:  header:            134  (2.9%)
             mode-partition:    536  (11.4%)
             transparency:      912 (62.9 dB)
 Residuals bytes  |segment 1|segment 2|segment 3|segment 4|  total
  intra4-coeffs:  |    2366 |      13 |      33 |      22 |    2434  (51.9%)
 intra16-coeffs:  |     291 |      17 |       0 |      94 |     402  (8.6%)
  chroma coeffs:  |     153 |      15 |       4 |      42 |     214  (4.6%)
    macroblocks:  |      69%|       4%|       2%|      25%|     256
      quantizer:  |      17 |      13 |       9 |       8 |
   filter level:  |      12 |       3 |       2 |       0 |
------------------+---------+---------+---------+---------+-----------------
 segments total:  |    2810 |      45 |      37 |     158 |    3050  (65.1%)
Lossless-alpha compressed size: 911 bytes
  * Header size: 60 bytes, image data size: 851
  * Precision Bits: histogram=3 transform=3 cache=0
  * Palette size:   69

Success
Reduction: 85% (went from 30 kb to 5 kb)

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
nice /usr/local/bin/cwebp -metadata none -q 85 -alpha_q '80' -near_lossless 60 -m 6 -low_memory '[doc-root]/wp-content/uploads/gourl/images/_img39.png' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img39.png.webp.lossless.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img39.png.webp.lossless.webp'
File:      [doc-root]/wp-content/uploads/gourl/images/_img39.png
Dimension: 256 x 256
Output:    16384 bytes (2.00 bpp)
Lossless-ARGB compressed size: 16384 bytes
  * Header size: 1229 bytes, image data size: 15130
  * Lossless features used: PREDICTION CROSS-COLOR-TRANSFORM SUBTRACT-GREEN
  * Precision Bits: histogram=3 transform=3 cache=7

Success
Reduction: 46% (went from 30 kb to 16 kb)

Picking lossy
cwebp succeeded :)

Converted image in 234 ms, reducing file size with 85% (went from 30 kb to 5 kb)
