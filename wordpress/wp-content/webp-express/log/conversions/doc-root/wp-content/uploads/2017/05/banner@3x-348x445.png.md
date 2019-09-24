WebP Express 0.15.3. Conversion triggered using bulk conversion, 2019-09-24 07:58:11

*WebP Convert 2.1.4*  ignited.
- PHP version: 7.3.1
- Server software: Apache

Stack converter ignited

Options:
------------
The following options have been set explicitly. Note: it is the resulting options after merging down the "jpeg" and "png" options and any converter-prefixed options.
- source: [doc-root]/wp-content/uploads/2017/05/banner@3x-348x445.png
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/05/banner@3x-348x445.png.webp
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
- source: [doc-root]/wp-content/uploads/2017/05/banner@3x-348x445.png
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/05/banner@3x-348x445.png.webp
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
nice /usr/local/bin/cwebp -metadata none -q 85 -alpha_q '80' -m 6 -low_memory '[doc-root]/wp-content/uploads/2017/05/banner@3x-348x445.png' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/05/banner@3x-348x445.png.webp.lossy.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/05/banner@3x-348x445.png.webp.lossy.webp'
File:      [doc-root]/wp-content/uploads/2017/05/banner@3x-348x445.png
Dimension: 348 x 445
Output:    8910 bytes Y-U-V-All-PSNR 45.36 46.24 45.08   45.44 dB
           (0.46 bpp)
block count:  intra4:        466  (75.65%)
              intra16:       150  (24.35%)
              skipped:        56  (9.09%)
bytes used:  header:            175  (2.0%)
             mode-partition:   1275  (14.3%)
 Residuals bytes  |segment 1|segment 2|segment 3|segment 4|  total
  intra4-coeffs:  |    3629 |      60 |      73 |     150 |    3912  (43.9%)
 intra16-coeffs:  |     444 |      26 |      11 |      70 |     551  (6.2%)
  chroma coeffs:  |    2487 |      64 |      56 |     362 |    2969  (33.3%)
    macroblocks:  |      47%|       3%|       3%|      48%|     616
      quantizer:  |      20 |      16 |      11 |       8 |
   filter level:  |       7 |       4 |       2 |      11 |
------------------+---------+---------+---------+---------+-----------------
 segments total:  |    6560 |     150 |     140 |     582 |    7432  (83.4%)

Success
Reduction: 95% (went from 161 kb to 9 kb)

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
nice /usr/local/bin/cwebp -metadata none -q 85 -alpha_q '80' -near_lossless 60 -m 6 -low_memory '[doc-root]/wp-content/uploads/2017/05/banner@3x-348x445.png' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/05/banner@3x-348x445.png.webp.lossless.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/05/banner@3x-348x445.png.webp.lossless.webp'
File:      [doc-root]/wp-content/uploads/2017/05/banner@3x-348x445.png
Dimension: 348 x 445
Output:    95808 bytes (4.95 bpp)
Lossless-ARGB compressed size: 95808 bytes
  * Header size: 3498 bytes, image data size: 92285
  * Lossless features used: PREDICTION CROSS-COLOR-TRANSFORM SUBTRACT-GREEN
  * Precision Bits: histogram=3 transform=3 cache=10

Success
Reduction: 42% (went from 161 kb to 94 kb)

Picking lossy
cwebp succeeded :)

Converted image in 509 ms, reducing file size with 95% (went from 161 kb to 9 kb)
