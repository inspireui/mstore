WebP Express 0.15.3. Conversion triggered using bulk conversion, 2019-09-24 07:53:40

*WebP Convert 2.1.4*  ignited.
- PHP version: 7.3.1
- Server software: Apache

Stack converter ignited

Options:
------------
The following options have been set explicitly. Note: it is the resulting options after merging down the "jpeg" and "png" options and any converter-prefixed options.
- source: [doc-root]/wp-content/uploads/2017/02/beet-caviar-recipe_h_big-348x445.jpg
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/beet-caviar-recipe_h_big-348x445.jpg.webp
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

Options:
------------
The following options have been set explicitly. Note: it is the resulting options after merging down the "jpeg" and "png" options and any converter-prefixed options.
- source: [doc-root]/wp-content/uploads/2017/02/beet-caviar-recipe_h_big-348x445.jpg
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/beet-caviar-recipe_h_big-348x445.jpg.webp
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
Quality of source is 90. This is higher than max-quality, so using max-quality instead (80)
The near-lossless option ignored for lossy
Trying to convert by executing the following command:
nice /usr/local/bin/cwebp -metadata none -q 80 -alpha_q '85' -m 6 -low_memory '[doc-root]/wp-content/uploads/2017/02/beet-caviar-recipe_h_big-348x445.jpg' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/beet-caviar-recipe_h_big-348x445.jpg.webp.lossy.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/beet-caviar-recipe_h_big-348x445.jpg.webp.lossy.webp'
File:      [doc-root]/wp-content/uploads/2017/02/beet-caviar-recipe_h_big-348x445.jpg
Dimension: 348 x 445
Output:    22682 bytes Y-U-V-All-PSNR 40.39 44.72 43.47   41.30 dB
           (1.17 bpp)
block count:  intra4:        590  (95.78%)
              intra16:        26  (4.22%)
              skipped:         0  (0.00%)
bytes used:  header:            140  (0.6%)
             mode-partition:   2887  (12.7%)
 Residuals bytes  |segment 1|segment 2|segment 3|segment 4|  total
  intra4-coeffs:  |   14852 |     568 |     534 |       0 |   15954  (70.3%)
 intra16-coeffs:  |     113 |      57 |     178 |       0 |     348  (1.5%)
  chroma coeffs:  |    3013 |     167 |     143 |       0 |    3323  (14.7%)
    macroblocks:  |      82%|       8%|      10%|       0%|     616
      quantizer:  |      21 |      15 |      11 |      11 |
   filter level:  |       9 |       3 |       0 |       0 |
------------------+---------+---------+---------+---------+-----------------
 segments total:  |   17978 |     792 |     855 |       0 |   19625  (86.5%)

Success
Reduction: 51% (went from 46 kb to 22 kb)

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
nice /usr/local/bin/cwebp -metadata none -q 80 -alpha_q '85' -near_lossless 60 -m 6 -low_memory '[doc-root]/wp-content/uploads/2017/02/beet-caviar-recipe_h_big-348x445.jpg' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/beet-caviar-recipe_h_big-348x445.jpg.webp.lossless.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/beet-caviar-recipe_h_big-348x445.jpg.webp.lossless.webp'
File:      [doc-root]/wp-content/uploads/2017/02/beet-caviar-recipe_h_big-348x445.jpg
Dimension: 348 x 445
Output:    115976 bytes (5.99 bpp)
Lossless-ARGB compressed size: 115976 bytes
  * Header size: 3276 bytes, image data size: 112675
  * Lossless features used: PREDICTION CROSS-COLOR-TRANSFORM SUBTRACT-GREEN
  * Precision Bits: histogram=3 transform=3 cache=10

Success
Reduction: -149% (went from 46 kb to 113 kb)

Picking lossy
cwebp succeeded :)

Converted image in 837 ms, reducing file size with 51% (went from 46 kb to 22 kb)
