# https://github.com/mdp/gibberish-aes/

@license Gibberish-AES 
A lightweight Javascript Libray for OpenSSL compatible AES CBC encryption.

Author: Mark Percival
Email: mark@mpercival.com
Copyright: Mark Percival - http://mpercival.com 2008

With thanks to:
Josh Davis - http://www.josh-davis.org/ecmaScrypt
Chris Veness - http://www.movable-type.co.uk/scripts/aes.html
Michel I. Gallant - http://www.jensign.com/
Jean-Luc Cooke <jlcooke@certainkey.com> 2012-07-12: added strhex + invertArr to compress G2X/G3X/G9X/GBX/GEX/SBox/SBoxInv/Rcon saving over 7KB, and added encString, decString, also made the MD5 routine more easlier compressible using yuicompressor.

License: MIT

Usage: GibberishAES.enc("secret", "password")
Outputs: AES Encrypted text encoded in Base64

