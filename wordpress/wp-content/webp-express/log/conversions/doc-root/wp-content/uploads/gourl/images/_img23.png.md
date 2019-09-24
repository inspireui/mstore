WebP Express 0.15.3. Conversion triggered using bulk conversion, 2019-09-24 06:00:39

*WebP Convert 2.1.4*  ignited.
- PHP version: 7.3.1
- Server software: Apache

Stack converter ignited

Options:
------------
The following options have been set explicitly. Note: it is the resulting options after merging down the "jpeg" and "png" options and any converter-prefixed options.
- source: [doc-root]/wp-content/uploads/gourl/images/_img23.png
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img23.png.webp
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
- source: [doc-root]/wp-content/uploads/gourl/images/_img23.png
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img23.png.webp
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
nice /usr/local/bin/cwebp -metadata none -q 85 -alpha_q '80' -m 6 -low_memory '[doc-root]/wp-content/uploads/gourl/images/_img23.png' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img23.png.webp.lossy.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img23.png.webp.lossy.webp'
File:      [doc-root]/wp-content/uploads/gourl/images/_img23.png
Dimension: 256 x 256 (with alpha)
Output:    3600 bytes Y-U-V-All-PSNR 48.07 47.66 48.87   48.12 dB
           (0.44 bpp)
block count:  intra4:         97  (37.89%)
              intra16:       159  (62.11%)
              skipped:       115  (44.92%)
bytes used:  header:            218  (6.1%)
             mode-partition:    535  (14.9%)
             transparency:      330 (99.0 dB)
 Residuals bytes  |segment 1|segment 2|segment 3|segment 4|  total
  intra4-coeffs:  |    1406 |       0 |       2 |       0 |    1408  (39.1%)
 intra16-coeffs:  |      36 |       6 |      10 |       0 |      52  (1.4%)
  chroma coeffs:  |    1000 |       1 |       1 |       0 |    1002  (27.8%)
    macroblocks:  |      71%|       0%|      29%|       0%|     256
      quantizer:  |      17 |      11 |       8 |       8 |
   filter level:  |       5 |       2 |       0 |       0 |
------------------+---------+---------+---------+---------+-----------------
 segments total:  |    2442 |       7 |      13 |       0 |    2462  (68.4%)
Lossless-alpha compressed size: 329 bytes
  * Header size: 41 bytes, image data size: 288
  * Precision Bits: histogram=3 transform=3 cache=0
  * Palette size:   29

Success
Reduction: 33% (went from 5356 bytes to 3600 bytes)

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
nice /usr/local/bin/cwebp -metadata none -q 85 -alpha_q '80' -near_lossless 60 -m 6 -low_memory '[doc-root]/wp-content/uploads/gourl/images/_img23.png' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img23.png.webp.lossless.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img23.png.webp.lossless.webp'
File:      [doc-root]/wp-content/uploads/gourl/images/_img23.png
Dimension: 256 x 256
Output:    2218 bytes (0.27 bpp)
Lossless-ARGB compressed size: 2218 bytes
  * Header size: 298 bytes, image data size: 1895
  * Lossless features used: PREDICTION CROSS-COLOR-TRANSFORM SUBTRACT-GREEN
  * Precision Bits: histogram=3 transform=3 cache=5

Success
Reduction: 59% (went from 5356 bytes to 2218 bytes)

Picking lossless
cwebp succeeded :)

Converted image in 192 ms, reducing file size with 59% (went from 5356 bytes to 2218 bytes)
