WebP Express 0.15.3. Conversion triggered using bulk conversion, 2019-09-24 05:53:58

*WebP Convert 2.1.4*  ignited.
- PHP version: 7.3.1
- Server software: Apache

Stack converter ignited

Options:
------------
The following options have been set explicitly. Note: it is the resulting options after merging down the "jpeg" and "png" options and any converter-prefixed options.
- source: [doc-root]/wp-content/uploads/2017/02/breakfast-in-bondi-smoothie.jpg
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/breakfast-in-bondi-smoothie.jpg.webp
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
- source: [doc-root]/wp-content/uploads/2017/02/breakfast-in-bondi-smoothie.jpg
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/breakfast-in-bondi-smoothie.jpg.webp
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
Quality of source could not be established (Imagick or GraphicsMagick is required) - Using default instead (70).
The near-lossless option ignored for lossy
Trying to convert by executing the following command:
nice /usr/local/bin/cwebp -metadata none -q 70 -alpha_q '85' -m 6 -low_memory '[doc-root]/wp-content/uploads/2017/02/breakfast-in-bondi-smoothie.jpg' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/breakfast-in-bondi-smoothie.jpg.webp.lossy.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/breakfast-in-bondi-smoothie.jpg.webp.lossy.webp'
File:      [doc-root]/wp-content/uploads/2017/02/breakfast-in-bondi-smoothie.jpg
Dimension: 1200 x 800
Output:    45990 bytes Y-U-V-All-PSNR 42.07 43.82 45.04   42.72 dB
           (0.38 bpp)
block count:  intra4:       2912  (77.65%)
              intra16:       838  (22.35%)
              skipped:         0  (0.00%)
bytes used:  header:            217  (0.5%)
             mode-partition:   9321  (20.3%)
 Residuals bytes  |segment 1|segment 2|segment 3|segment 4|  total
  intra4-coeffs:  |   12336 |    5013 |    2933 |     889 |   21171  (46.0%)
 intra16-coeffs:  |    2207 |    1775 |    1730 |     499 |    6211  (13.5%)
  chroma coeffs:  |    4817 |    2136 |    1551 |     540 |    9044  (19.7%)
    macroblocks:  |      50%|      26%|      18%|       6%|    3750
      quantizer:  |      34 |      25 |      18 |      16 |
   filter level:  |      50 |      33 |      21 |      34 |
------------------+---------+---------+---------+---------+-----------------
 segments total:  |   19360 |    8924 |    6214 |    1928 |   36426  (79.2%)

Success
Reduction: 17% (went from 54 kb to 45 kb)

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
nice /usr/local/bin/cwebp -metadata none -q 70 -alpha_q '85' -near_lossless 60 -m 6 -low_memory '[doc-root]/wp-content/uploads/2017/02/breakfast-in-bondi-smoothie.jpg' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/breakfast-in-bondi-smoothie.jpg.webp.lossless.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/2017/02/breakfast-in-bondi-smoothie.jpg.webp.lossless.webp'
File:      [doc-root]/wp-content/uploads/2017/02/breakfast-in-bondi-smoothie.jpg
Dimension: 1200 x 800
Output:    551676 bytes (4.60 bpp)
Lossless-ARGB compressed size: 551676 bytes
  * Header size: 3723 bytes, image data size: 547928
  * Lossless features used: PREDICTION CROSS-COLOR-TRANSFORM SUBTRACT-GREEN
  * Precision Bits: histogram=5 transform=4 cache=10

Success
Reduction: -896% (went from 54 kb to 539 kb)

Picking lossy
cwebp succeeded :)

Converted image in 1626 ms, reducing file size with 17% (went from 54 kb to 45 kb)
