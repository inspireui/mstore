WebP Express 0.15.3. Conversion triggered using bulk conversion, 2019-09-24 05:47:38

*WebP Convert 2.1.4*  ignited.
- PHP version: 7.3.1
- Server software: Apache

Stack converter ignited

Options:
------------
The following options have been set explicitly. Note: it is the resulting options after merging down the "jpeg" and "png" options and any converter-prefixed options.
- source: [doc-root]/wp-content/uploads/2017/02/turmeric-goats-curd-onion-seed-loaf-1024x683.jpg
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/turmeric-goats-curd-onion-seed-loaf-1024x683.jpg.webp
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
- source: [doc-root]/wp-content/uploads/2017/02/turmeric-goats-curd-onion-seed-loaf-1024x683.jpg
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/turmeric-goats-curd-onion-seed-loaf-1024x683.jpg.webp
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
Quality of source is 82. This is higher than max-quality, so using max-quality instead (80)
The near-lossless option ignored for lossy
Trying to convert by executing the following command:
nice /usr/local/bin/cwebp -metadata none -q 80 -alpha_q '85' -m 6 -low_memory '[doc-root]/wp-content/uploads/2017/02/turmeric-goats-curd-onion-seed-loaf-1024x683.jpg' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/turmeric-goats-curd-onion-seed-loaf-1024x683.jpg.webp.lossy.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/turmeric-goats-curd-onion-seed-loaf-1024x683.jpg.webp.lossy.webp'
File:      [doc-root]/wp-content/uploads/2017/02/turmeric-goats-curd-onion-seed-loaf-1024x683.jpg
Dimension: 1024 x 683
Output:    82070 bytes Y-U-V-All-PSNR 40.73 43.63 44.86   41.61 dB
           (0.94 bpp)
block count:  intra4:       2283  (82.96%)
              intra16:       469  (17.04%)
              skipped:         6  (0.22%)
bytes used:  header:            253  (0.3%)
             mode-partition:  11454  (14.0%)
 Residuals bytes  |segment 1|segment 2|segment 3|segment 4|  total
  intra4-coeffs:  |   48753 |    1692 |    1672 |    1405 |   53522  (65.2%)
 intra16-coeffs:  |    1048 |     704 |     961 |     969 |    3682  (4.5%)
  chroma coeffs:  |   11115 |     548 |     685 |     785 |   13133  (16.0%)
    macroblocks:  |      65%|       9%|      10%|      16%|    2752
      quantizer:  |      24 |      17 |      12 |      11 |
   filter level:  |      21 |       4 |       2 |       8 |
------------------+---------+---------+---------+---------+-----------------
 segments total:  |   60916 |    2944 |    3318 |    3159 |   70337  (85.7%)

Success
Reduction: 35% (went from 122 kb to 80 kb)

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
nice /usr/local/bin/cwebp -metadata none -q 80 -alpha_q '85' -near_lossless 60 -m 6 -low_memory '[doc-root]/wp-content/uploads/2017/02/turmeric-goats-curd-onion-seed-loaf-1024x683.jpg' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/turmeric-goats-curd-onion-seed-loaf-1024x683.jpg.webp.lossless.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/turmeric-goats-curd-onion-seed-loaf-1024x683.jpg.webp.lossless.webp'
File:      [doc-root]/wp-content/uploads/2017/02/turmeric-goats-curd-onion-seed-loaf-1024x683.jpg
Dimension: 1024 x 683
Output:    440494 bytes (5.04 bpp)
Lossless-ARGB compressed size: 440494 bytes
  * Header size: 4455 bytes, image data size: 436014
  * Lossless features used: PREDICTION CROSS-COLOR-TRANSFORM SUBTRACT-GREEN
  * Precision Bits: histogram=5 transform=4 cache=10

Success
Reduction: -251% (went from 122 kb to 430 kb)

Picking lossy
cwebp succeeded :)

Converted image in 1375 ms, reducing file size with 35% (went from 122 kb to 80 kb)
