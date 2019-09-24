WebP Express 0.15.3. Conversion triggered using bulk conversion, 2019-09-24 05:46:23

*WebP Convert 2.1.4*  ignited.
- PHP version: 7.3.1
- Server software: Apache

Stack converter ignited

Options:
------------
The following options have been set explicitly. Note: it is the resulting options after merging down the "jpeg" and "png" options and any converter-prefixed options.
- source: [doc-root]/wp-content/uploads/2017/02/quinoa-infused-with-orange-and-chai-1024x683.jpg
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/quinoa-infused-with-orange-and-chai-1024x683.jpg.webp
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
- source: [doc-root]/wp-content/uploads/2017/02/quinoa-infused-with-orange-and-chai-1024x683.jpg
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/quinoa-infused-with-orange-and-chai-1024x683.jpg.webp
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
nice /usr/local/bin/cwebp -metadata none -q 80 -alpha_q '85' -m 6 -low_memory '[doc-root]/wp-content/uploads/2017/02/quinoa-infused-with-orange-and-chai-1024x683.jpg' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/quinoa-infused-with-orange-and-chai-1024x683.jpg.webp.lossy.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/quinoa-infused-with-orange-and-chai-1024x683.jpg.webp.lossy.webp'
File:      [doc-root]/wp-content/uploads/2017/02/quinoa-infused-with-orange-and-chai-1024x683.jpg
Dimension: 1024 x 683
Output:    68796 bytes Y-U-V-All-PSNR 41.23 44.16 45.19   42.10 dB
           (0.79 bpp)
block count:  intra4:       2343  (85.14%)
              intra16:       409  (14.86%)
              skipped:        10  (0.36%)
bytes used:  header:            205  (0.3%)
             mode-partition:  10623  (15.4%)
 Residuals bytes  |segment 1|segment 2|segment 3|segment 4|  total
  intra4-coeffs:  |   33939 |    3231 |    2398 |     933 |   40501  (58.9%)
 intra16-coeffs:  |    1474 |     754 |    1322 |     654 |    4204  (6.1%)
  chroma coeffs:  |   11181 |     843 |     786 |     426 |   13236  (19.2%)
    macroblocks:  |      66%|      13%|      13%|       9%|    2752
      quantizer:  |      23 |      16 |      11 |      11 |
   filter level:  |      20 |      11 |       5 |       0 |
------------------+---------+---------+---------+---------+-----------------
 segments total:  |   46594 |    4828 |    4506 |    2013 |   57941  (84.2%)

Success
Reduction: 42% (went from 115 kb to 67 kb)

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
nice /usr/local/bin/cwebp -metadata none -q 80 -alpha_q '85' -near_lossless 60 -m 6 -low_memory '[doc-root]/wp-content/uploads/2017/02/quinoa-infused-with-orange-and-chai-1024x683.jpg' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/quinoa-infused-with-orange-and-chai-1024x683.jpg.webp.lossless.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/quinoa-infused-with-orange-and-chai-1024x683.jpg.webp.lossless.webp'
File:      [doc-root]/wp-content/uploads/2017/02/quinoa-infused-with-orange-and-chai-1024x683.jpg
Dimension: 1024 x 683
Output:    454024 bytes (5.19 bpp)
Lossless-ARGB compressed size: 454024 bytes
  * Header size: 4479 bytes, image data size: 449519
  * Lossless features used: PREDICTION CROSS-COLOR-TRANSFORM SUBTRACT-GREEN
  * Precision Bits: histogram=5 transform=4 cache=10

Success
Reduction: -286% (went from 115 kb to 443 kb)

Picking lossy
cwebp succeeded :)

Converted image in 1435 ms, reducing file size with 42% (went from 115 kb to 67 kb)
