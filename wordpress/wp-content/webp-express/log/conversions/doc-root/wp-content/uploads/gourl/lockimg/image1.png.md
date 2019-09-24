WebP Express 0.15.3. Conversion triggered using bulk conversion, 2019-09-24 06:00:42

*WebP Convert 2.1.4*  ignited.
- PHP version: 7.3.1
- Server software: Apache

Stack converter ignited

Options:
------------
The following options have been set explicitly. Note: it is the resulting options after merging down the "jpeg" and "png" options and any converter-prefixed options.
- source: [doc-root]/wp-content/uploads/gourl/lockimg/image1.png
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/lockimg/image1.png.webp
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
- source: [doc-root]/wp-content/uploads/gourl/lockimg/image1.png
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/lockimg/image1.png.webp
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
nice /usr/local/bin/cwebp -metadata none -q 85 -alpha_q '80' -m 6 -low_memory '[doc-root]/wp-content/uploads/gourl/lockimg/image1.png' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/lockimg/image1.png.webp.lossy.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/lockimg/image1.png.webp.lossy.webp'
File:      [doc-root]/wp-content/uploads/gourl/lockimg/image1.png
Dimension: 555 x 445 (with alpha)
Output:    9750 bytes Y-U-V-All-PSNR 50.16 52.50 53.51   50.91 dB
           (0.32 bpp)
block count:  intra4:        155  (15.82%)
              intra16:       825  (84.18%)
              skipped:       645  (65.82%)
bytes used:  header:            229  (2.3%)
             mode-partition:   1259  (12.9%)
             transparency:     4376 (63.8 dB)
 Residuals bytes  |segment 1|segment 2|segment 3|segment 4|  total
  intra4-coeffs:  |    2608 |      78 |     128 |      18 |    2832  (29.0%)
 intra16-coeffs:  |      34 |      12 |     137 |       6 |     189  (1.9%)
  chroma coeffs:  |     664 |      59 |      56 |      33 |     812  (8.3%)
    macroblocks:  |      19%|      10%|      14%|      57%|     980
      quantizer:  |      20 |      17 |      14 |      10 |
   filter level:  |       7 |       4 |      10 |       0 |
------------------+---------+---------+---------+---------+-----------------
 segments total:  |    3306 |     149 |     321 |      57 |    3833  (39.3%)
Lossless-alpha compressed size: 4375 bytes
  * Header size: 122 bytes, image data size: 4253
  * Lossless features used: PREDICTION
  * Precision Bits: histogram=4 transform=4 cache=0
  * Palette size:   85

Success
Reduction: 71% (went from 33 kb to 10 kb)

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
nice /usr/local/bin/cwebp -metadata none -q 85 -alpha_q '80' -near_lossless 60 -m 6 -low_memory '[doc-root]/wp-content/uploads/gourl/lockimg/image1.png' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/lockimg/image1.png.webp.lossless.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/lockimg/image1.png.webp.lossless.webp'
File:      [doc-root]/wp-content/uploads/gourl/lockimg/image1.png
Dimension: 555 x 445
Output:    10970 bytes (0.36 bpp)
Lossless-ARGB compressed size: 10970 bytes
  * Header size: 753 bytes, image data size: 10191
  * Lossless features used: PREDICTION CROSS-COLOR-TRANSFORM SUBTRACT-GREEN
  * Precision Bits: histogram=4 transform=4 cache=7

Success
Reduction: 67% (went from 33 kb to 11 kb)

Picking lossy
cwebp succeeded :)

Converted image in 404 ms, reducing file size with 71% (went from 33 kb to 10 kb)
