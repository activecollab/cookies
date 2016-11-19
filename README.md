# Cookies Library

[![Build Status](https://travis-ci.org/activecollab/cookies.svg?branch=master)](https://travis-ci.org/activecollab/cookeis)

Features:

1. Configure from URL. Configure domain, path, and secure values based on URL of your application,
1. Automatically prefix all cookie names,
1. Automatically encrypt cookie values. By default, AES 256 CBC is used, but you can provide any encryption system (it needs to implement `ActiveCollab\Encryptor\EncryptorInterface` from `activecollab/utils` package).
