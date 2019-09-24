WebP Express 0.15.3. Conversion triggered using bulk conversion, 2019-09-24 06:00:01

*WebP Convert 2.1.4*  ignited.
- PHP version: 7.3.1
- Server software: Apache

Stack converter ignited

Options:
------------
The following options have been set explicitly. Note: it is the resulting options after merging down the "jpeg" and "png" options and any converter-prefixed options.
- source: [doc-root]/wp-content/uploads/gourl/images/_img07.png
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img07.png.webp
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
- source: [doc-root]/wp-content/uploads/gourl/images/_img07.png
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img07.png.webp
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
nice /usr/local/bin/cwebp -metadata none -q 85 -alpha_q '80' -m 6 -low_memory '[doc-root]/wp-content/uploads/gourl/images/_img07.png' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img07.png.webp.lossy.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img07.png.webp.lossy.webp'
File:      [doc-root]/wp-content/uploads/gourl/images/_img07.png
Dimension: 256 x 256 (with alpha)
Output:    5348 bytes Y-U-V-All-PSNR 47.59 46.46 46.75   47.23 dB
           (0.65 bpp)
block count:  intra4:         67  (26.17%)
              intra16:       189  (73.83%)
              skipped:        83  (32.42%)
bytes used:  header:            113  (2.1%)
             mode-partition:    446  (8.3%)
             transparency:     2477 (63.3 dB)
 Residuals bytes  |segment 1|segment 2|segment 3|segment 4|  total
  intra4-coeffs:  |     866 |      25 |      15 |      19 |     925  (17.3%)
 intra16-coeffs:  |      81 |      13 |      47 |      77 |     218  (4.1%)
  chroma coeffs:  |     904 |      17 |      90 |     104 |    1115  (20.8%)
    macroblocks:  |      48%|       2%|       5%|      46%|     256
      quantizer:  |      20 |      16 |      10 |       8 |
   filter level:  |      13 |       4 |       0 |       0 |
------------------+---------+---------+---------+---------+-----------------
 segments total:  |    1851 |      55 |     152 |     200 |    2258  (42.2%)
Lossless-alpha compressed size: 2476 bytes
  * Header size: 66 bytes, image data size: 2410
  * Precision Bits: histogram=3 transform=3 cache=0
  * Palette size:   181

Success
Reduction: 67% (went from 16 kb to 5 kb)

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
nice /usr/local/bin/cwebp -metadata none -q 85 -alpha_q '80' -near_lossless 60 -m 6 -low_memory '[doc-root]/wp-content/uploads/gourl/images/_img07.png' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img07.png.webp.lossless.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/uploads/gourl/images/_img07.png.webp.lossless.webp'
File:      [doc-root]/wp-content/uploads/gourl/images/_img07.png
Dimension: 256 x 256
Output:    8606 bytes (1.05 bpp)
Lossless-ARGB compressed size: 8606 bytes
  * Header size: 586 bytes, image data size: 7994
  * Lossless features used: PREDICTION CROSS-COLOR-TRANSFORM
  * Precision Bits: histogram=3 transform=3 cache=9

Success
Reduction: 48% (went from 16 kb to 8 kb)

Picking lossy
cwebp succeeded :)

Converted image in 199 ms, reducing file size with 67% (went from 16 kb to 5 kb)
