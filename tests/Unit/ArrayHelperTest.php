<?php

use Marshall\AdventOfCode\ArrayHelper;

describe('ArrayHelper', function () {
    test('recursively finds needles', function () {
        expect(ArrayHelper::contains('needle', [
            'notANeedle',
            [
                'notANeedle',
                [
                    'needle',
                ]
            ]
        ]))->toBeTrue();
    });
    test('recursively finds a lack of needles', function () {
        expect(ArrayHelper::contains('needle', [
            'notANeedle',
            [
                'notANeedle',
                [
                    'stillNotANeedle',
                ]
            ]
        ]))->toBeFalse();
    });
});
