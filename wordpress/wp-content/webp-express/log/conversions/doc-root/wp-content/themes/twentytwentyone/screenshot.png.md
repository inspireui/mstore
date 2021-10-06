WebP Express 0.20.1. Conversion triggered with the conversion script (wod/webp-on-demand.php), 2021-10-06 09:49:54

*WebP Convert 2.6.0*  ignited.
- PHP version: 7.4.21
- Server software: Apache/2.4.48 (Unix) OpenSSL/1.0.2u PHP/7.4.21 mod_wsgi/3.5 Python/2.7.13 mod_fastcgi/mod_fastcgi-SNAP-0910052141 mod_perl/2.0.11 Perl/v5.30.1

Stack converter ignited
Destination folder does not exist. Creating folder: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/themes/twentytwentyone

Options:
------------
The following options have been set explicitly. Note: it is the resulting options after merging down the "jpeg" and "png" options and any converter-prefixed options.
- source: [doc-root]/wp-content/themes/twentytwentyone/screenshot.png
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/themes/twentytwentyone/screenshot.png.webp
- log-call-arguments: true
- converters: (array of 9 items)

The following options have not been explicitly set, so using the following defaults:
- auto-limit: true
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
- source: [doc-root]/wp-content/themes/twentytwentyone/screenshot.png
- destination: [doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/themes/twentytwentyone/screenshot.png.webp
- alpha-quality: 80
- encoding: "auto"
- low-memory: true
- log-call-arguments: true
- metadata: "none"
- method: 6
- near-lossless: 60
- quality: 85
- use-nice: true
- try-common-system-paths: true
- try-supplied-binary-for-os: true
- command-line-options: ""

The following options have not been explicitly set, so using the following defaults:
- auto-limit: true
- auto-filter: false
- default-quality: 85
- max-quality: 85
- preset: "none"
- size-in-percentage: null (not set)
- sharp-yuv: true
- skip: false
- try-cwebp: true
- try-discovering-cwebp: true
- rel-path-to-precompiled-binaries: *****
- skip-these-precompiled-binaries: ""
------------

Encoding is set to auto - converting to both lossless and lossy and selecting the smallest file

Converting to lossy
Looking for cwebp binaries.
Discovering if a plain cwebp call works (to skip this step, disable the "try-cwebp" option)
- Executing: cwebp -version 2>&1. Result: *Exec failed* (the cwebp binary was not found at path: cwebp, or it had missing library dependencies)
Nope a plain cwebp call does not work (spent 4 ms)
Discovering binaries using "which -a cwebp" command. (to skip this step, disable the "try-discovering-cwebp" option)
Found 0 binaries (spent 11 ms)
Discovering binaries by peeking in common system paths (to skip this step, disable the "try-common-system-paths" option)
Found 1 binaries (spent 0 ms)
- /usr/local/bin/cwebp
Discovering binaries which are distributed with the webp-convert library (to skip this step, disable the "try-supplied-binary-for-os" option)
Checking if we have a supplied precompiled binary for your OS (Darwin)... We do.
Found 1 binaries (spent 0 ms)
- [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/webp-convert/src/Convert/Converters/Binaries/cwebp-110-mac-10_15
Discovering cwebp binaries took: 16 ms

Detecting versions of the cwebp binaries found (except supplied binaries)
- Executing: /usr/local/bin/cwebp -version 2>&1. Result: version: *1.2.0*
Detecting versions took: 10 ms
Binaries ordered by version number.
- /usr/local/bin/cwebp: (version: 1.2.0)
- [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/webp-convert/src/Convert/Converters/Binaries/cwebp-110-mac-10_15: (version: 1.1.0)
Starting conversion, using the first of these. If that should fail, the next will be tried and so on.
Creating command line options for version: 1.2.0
Bypassing auto-limit (it is only active for jpegs)
Quality: 85. 
The near-lossless option ignored for lossy
Trying to convert by executing the following command:
nice /usr/local/bin/cwebp -metadata none -q 85 -alpha_q '80' -sharp_yuv -m 6 -low_memory '[doc-root]/wp-content/themes/twentytwentyone/screenshot.png' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/themes/twentytwentyone/screenshot.png.webp.lossy.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/themes/twentytwentyone/screenshot.png.webp.lossy.webp'
File:      [doc-root]/wp-content/themes/twentytwentyone/screenshot.png
Dimension: 1200 x 900
Output:    54490 bytes Y-U-V-All-PSNR 47.19 49.51 53.07   48.12 dB
           (0.40 bpp)
block count:  intra4:        973  (22.76%)
              intra16:      3302  (77.24%)
              skipped:      3165  (74.04%)
bytes used:  header:            346  (0.6%)
             mode-partition:   6607  (12.1%)
 Residuals bytes  |segment 1|segment 2|segment 3|segment 4|  total
  intra4-coeffs:  |   16264 |    9906 |   14673 |     480 |   41323  (75.8%)
 intra16-coeffs:  |       0 |       0 |     318 |      26 |     344  (0.6%)
  chroma coeffs:  |    1594 |    1257 |    2876 |     114 |    5841  (10.7%)
    macroblocks:  |       4%|       4%|      16%|      75%|    4275
      quantizer:  |      20 |      18 |      16 |      12 |
   filter level:  |       7 |       3 |      11 |       2 |
------------------+---------+---------+---------+---------+-----------------
 segments total:  |   17858 |   11163 |   17867 |     620 |   47508  (87.2%)

Executing cwebp binary took: 217 ms

Success
Reduction: 81% (went from 276 kb to 53 kb)

Converting to lossless
Looking for cwebp binaries.
Discovering if a plain cwebp call works (to skip this step, disable the "try-cwebp" option)
- Executing: cwebp -version 2>&1. Result: *Exec failed* (the cwebp binary was not found at path: cwebp, or it had missing library dependencies)
Nope a plain cwebp call does not work (spent 3 ms)
Discovering binaries using "which -a cwebp" command. (to skip this step, disable the "try-discovering-cwebp" option)
Found 0 binaries (spent 6 ms)
Discovering binaries by peeking in common system paths (to skip this step, disable the "try-common-system-paths" option)
Found 1 binaries (spent 0 ms)
- /usr/local/bin/cwebp
Discovering binaries which are distributed with the webp-convert library (to skip this step, disable the "try-supplied-binary-for-os" option)
Checking if we have a supplied precompiled binary for your OS (Darwin)... We do.
Found 1 binaries (spent 0 ms)
- [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/webp-convert/src/Convert/Converters/Binaries/cwebp-110-mac-10_15
Discovering cwebp binaries took: 9 ms

Detecting versions of the cwebp binaries found (except supplied binaries)
- Executing: /usr/local/bin/cwebp -version 2>&1. Result: version: *1.2.0*
Detecting versions took: 4 ms
Binaries ordered by version number.
- /usr/local/bin/cwebp: (version: 1.2.0)
- [doc-root]/wp-content/plugins/webp-express/vendor/rosell-dk/webp-convert/src/Convert/Converters/Binaries/cwebp-110-mac-10_15: (version: 1.1.0)
Starting conversion, using the first of these. If that should fail, the next will be tried and so on.
Creating command line options for version: 1.2.0
Trying to convert by executing the following command:
nice /usr/local/bin/cwebp -metadata none -q 85 -alpha_q '80' -near_lossless 60 -sharp_yuv -m 6 -low_memory '[doc-root]/wp-content/themes/twentytwentyone/screenshot.png' -o '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/themes/twentytwentyone/screenshot.png.webp.lossless.webp' 2>&1

*Output:* 
Saving file '[doc-root]/wp-content/webp-express/webp-images/doc-root/wp-content/themes/twentytwentyone/screenshot.png.webp.lossless.webp'
File:      [doc-root]/wp-content/themes/twentytwentyone/screenshot.png
Dimension: 1200 x 900
Output:    122874 bytes (0.91 bpp)
Lossless-ARGB compressed size: 122874 bytes
  * Header size: 2085 bytes, image data size: 120764
  * Lossless features used: PREDICTION CROSS-COLOR-TRANSFORM SUBTRACT-GREEN
  * Precision Bits: histogram=5 transform=4 cache=10

Executing cwebp binary took: 711 ms

Success
Reduction: 57% (went from 276 kb to 120 kb)

Picking lossy
cwebp succeeded :)

Converted image in 980 ms, reducing file size with 81% (went from 276 kb to 53 kb)
