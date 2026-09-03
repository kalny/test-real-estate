<?php

it('application returns a successful response', function () {
    $this->get('/')->assertOk();
});
