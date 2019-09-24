WebP Express 0.15.3. Conversion triggered using bulk conversion, 2019-09-24 07:58:12

*WebP Convert 2.1.4*  ignited.
- PHP version: 7.3.1
- Server software: Apache

Stack converter ignited

Options:
------------
The following options have been set explicitly. Note: it is the resulting options after merging down the "jpeg" and "png" options and any converter-prefixed options.
- source: [doc-root]/wp-content/uploads/2017/05/banner@3x-568x280.png
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/05/banner@3x-568x280.png.webp
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
- source: [doc-root]/wp-content/uploads/2017/05/banner@3x-568x280.png
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/05/banner@3x-568x280.png.webp
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
nice /usr/local/bin/cwebp -metadata none -q 85 -alpha_q '80' -m 6 -low_memory '[doc-root]/wp-content/uploads/2017/05/banner@3x-568x280.png' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/05/banner@3x-568x280.png.webp.lossy.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/05/banner@3x-568x280.png.webp.lossy.webp'
File:      [doc-root]/wp-content/uploads/2017/05/banner@3x-568x280.png
Dimension: 568 x 280
Output:    5544 bytes Y-U-V-All-PSNR 47.91 49.56 47.38   48.05 dB
           (0.28 bpp)
block count:  intra4:        561  (86.57%)
              intra16:        87  (13.43%)
              skipped:       119  (18.36%)
bytes used:  header:            156  (2.8%)
             mode-partition:    886  (16.0%)
 Residuals bytes  |segment 1|segment 2|segment 3|segment 4|  total
  intra4-coeffs:  |    2216 |      48 |      17 |      51 |    2332  (42.1%)
 intra16-coeffs:  |      90 |       0 |      22 |      11 |     123  (2.2%)
  chroma coeffs:  |    1561 |      32 |      20 |     404 |    2017  (36.4%)
    macroblocks:  |      27%|       1%|       1%|      71%|     648
      quantizer:  |      20 |      18 |      15 |      10 |
   filter level:  |       9 |       4 |       7 |       0 |
------------------+---------+---------+---------+---------+-----------------
 segments total:  |    3867 |      80 |      59 |     466 |    4472  (80.7%)

Success
Reduction: 96% (went from 138 kb to 5 kb)

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
nice /usr/local/bin/cwebp -metadata none -q 85 -alpha_q '80' -near_lossless 60 -m 6 -low_memory '[doc-root]/wp-content/uploads/2017/05/banner@3x-568x280.png' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/05/banner@3x-568x280.png.webp.lossless.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/05/banner@3x-568x280.png.webp.lossless.webp'
File:      [doc-root]/wp-content/uploads/2017/05/banner@3x-568x280.png
Dimension: 568 x 280
Output:    72858 bytes (3.66 bpp)
Lossless-ARGB compressed size: 72858 bytes
  * Header size: 2379 bytes, image data size: 70453
  * Lossless features used: PREDICTION CROSS-COLOR-TRANSFORM
  * Precision Bits: histogram=3 transform=3 cache=10

Success
Reduction: 48% (went from 138 kb to 71 kb)

Picking lossy
cwebp succeeded :)

Converted image in 444 ms, reducing file size with 96% (went from 138 kb to 5 kb)
