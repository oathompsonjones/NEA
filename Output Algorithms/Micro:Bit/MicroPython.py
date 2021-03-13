from microbit import *
import math


def play(animation):
    for frame in animation:
        bits = [255 & frame[0]]
        for i in range(len(frame) - 1):
            for j in range(4):
                bits.append(255 & frame[i + 1] << j * 8 >> 24)
        for i in range(len(bits)):
            x = i % 5
            y = i // 5
            display.set_pixel(x, y, bits[i] * 9 // 255)
        sleep(1000 / 1)
        display.clear()


while True:
    if button_a.is_pressed():
        play([
            [
                0b00000000000000000000000000000000,
                0b00000000111111110000000000000000,
                0b00000000000000001111111100000000,
                0b00000000111111111111111111111111,
                0b11111111111111110000000000000000,
                0b11111111000000000000000000000000,
                0b00000000111111110000000000000000
            ],
            [
                0b00000000000000000000000011111111,
                0b00000000000000000000000011111111,
                0b00000000111111110000000011111111,
                0b00000000000000000000000011111111,
                0b00000000000000000000000011111111,
                0b00000000111111110000000011111111,
                0b00000000000000000000000011111111
            ],
            [
                0b00000000000000000000000011111111,
                0b11111111111111111111111111111111,
                0b11111111000000000000000000000000,
                0b11111111111111110000000000000000,
                0b00000000111111111111111100000000,
                0b00000000000000001111111111111111,
                0b11111111111111111111111111111111
            ],
            [
                0b00000000000000000000000000000000,
                0b11111111111111111111111100000000,
                0b11111111000000000000000000000000,
                0b11111111111111110000000000000000,
                0b00000000111111111111111100000000,
                0b00000000000000001111111100000000,
                0b11111111111111111111111100000000
            ],
            [
                0b00000000000000000000000000000000,
                0b11111111111111111111111100000000,
                0b11111111111111111111111111111111,
                0b11111111111111111111111111111111,
                0b11111111111111111111111111111111,
                0b11111111111111111111111100000000,
                0b11111111111111111111111100000000
            ],
            [
                0b00000000000000000000000000000000,
                0b11111111111111111111111100000000,
                0b11111111111111110000000000000000,
                0b11111111111111110000000011111111,
                0b00000000111111111111111100000000,
                0b00000000111111111111111100000000,
                0b11111111111111111111111100000000
            ],
            [
                0b00000000000000000000000000000000,
                0b00000000000000000000000000000000,
                0b00000000000000000000000000000000,
                0b00000000000000000000000011111111,
                0b00000000000000000000000000000000,
                0b00000000000000000000000000000000,
                0b00000000000000000000000000000000
            ]
        ])
