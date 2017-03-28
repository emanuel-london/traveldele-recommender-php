<?php

namespace Kooyara\RecommenderSystem;


class Config
{
    const api_version = '1.0';

    const test_protocol = 'http';
    const test_host = '127.0.0.1:5000';
    const test_version = '1.0';
    const test_client_id = 'X58P5YzMtBjbdq6';
    const test_client_secret = 'J2bR70oykhNnkFq5xbrha27dfBo7I5';

    const development_protocol = 'http';
    const development_host = 'rs-dev.kooyara.com';
    const development_version = '1.0';

    const production_host = 'rs.kooyara.com';
    const production_protocol = 'https';
    const production_version = '1.0';

    const access_token_url = 'oauth/token';
    const grant_type = 'client_credentials';
}