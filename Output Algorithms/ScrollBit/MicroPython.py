# Download the scrollbit library from https://github.com/pimoroni/micropython-scrollbit/blob/master/library/scrollbit.py
# Load/Save > Project Files > Add File

import scrollbit
from microbit import *

def play(animation):
	for frame in animation:
		bits = [
			255 & frame[0] >> 16,
			255 & frame[0] >> 8,
			255 & frame[0]
		]
		for i in range(len(frame) - 1):
			for j in range(4):
				bits.append(255 & frame[i + 1] >> 24 - j * 8)
		for i in range(len(bits)):
			x = i % 17
			y = i // 17
			scrollbit.set_pixel(x, y, bits[i])
		scrollbit.show()
		sleep(1000 / 1)
		scrollbit.clear()

while True:
	if button_a.is_pressed():
		play(
			[
                [
                    0b00000000111111111111111100000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b11111111111111110000000000000000,
                    0b11111111111111111111111100000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000011111111,
                    0b11111111000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b11111111111111110000000000000000,
                    0b00000000111111111111111111111111,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000011111111,
                    0b11111111111111110000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000001111111111111111,
                    0b00000000000000000000000011111111,
                    0b11111111111111110000000000000000,
                    0b00000000000000000000000000000000,
                    0b11111111111111111111111100000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000011111111,
                    0b11111111000000000000000011111111,
                    0b11111111000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000001111111111111111
                ],
                [
                    0b00000000111111111111111111111111,
                    0b11111111111111111111111111111111,
                    0b11111111111111111111111111111111,
                    0b11111111111111111111111111111111,
                    0b11111111111111111111111100000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000001111111111111111,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000011111111,
                    0b11111111000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b11111111111111110000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000111111111111111100000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000001111111111111111,
                    0b11111111111111111111111111111111,
                    0b11111111111111111111111111111111,
                    0b11111111111111111111111111111111,
                    0b11111111111111111111111111111111
                ],
                [
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000111111110000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000001111111100000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000011111111,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b11111111111111111111111111111111,
                    0b11111111111111111111111111111111,
                    0b11111111111111111111111111111111,
                    0b11111111111111111111111111111111,
                    0b11111111000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000111111110000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000001111111100000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000011111111,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000
                ],
                [
                    0b00000000110010001100100000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b11001000110010000000000000000000,
                    0b11001000110010001100100000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000011001000,
                    0b11001000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b11001000110010000000000000000000,
                    0b00000000110010001100100011001000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000011001000,
                    0b11001000110010000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000001100100011001000,
                    0b00000000000000000000000011001000,
                    0b11001000110010000000000000000000,
                    0b00000000000000000000000000000000,
                    0b11001000110010001100100000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000011001000,
                    0b11001000000000000000000011001000,
                    0b11001000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000001100100011001000
                ],
                [
                    0b00000000110010001100100011001000,
                    0b11001000110010001100100011001000,
                    0b11001000110010001100100011001000,
                    0b11001000110010001100100011001000,
                    0b11001000110010001100100000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000001100100011001000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000011001000,
                    0b11001000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b11001000110010000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000110010001100100000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000001100100011001000,
                    0b11001000110010001100100011001000,
                    0b11001000110010001100100011001000,
                    0b11001000110010001100100011001000,
                    0b11001000110010001100100011001000
                ],
                [
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000110010000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000001100100000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000011001000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b11001000110010001100100011001000,
                    0b11001000110010001100100011001000,
                    0b11001000110010001100100011001000,
                    0b11001000110010001100100011001000,
                    0b11001000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000110010000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000001100100000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000011001000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000
                ],
                [
                    0b00000000011001000110010000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b01100100011001000000000000000000,
                    0b01100100011001000110010000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000001100100,
                    0b01100100000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b01100100011001000000000000000000,
                    0b00000000011001000110010001100100,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000001100100,
                    0b01100100011001000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000110010001100100,
                    0b00000000000000000000000001100100,
                    0b01100100011001000000000000000000,
                    0b00000000000000000000000000000000,
                    0b01100100011001000110010000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000001100100,
                    0b01100100000000000000000001100100,
                    0b01100100000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000110010001100100
                ],
                [
                    0b00000000011001000110010001100100,
                    0b01100100011001000110010001100100,
                    0b01100100011001000110010001100100,
                    0b01100100011001000110010001100100,
                    0b01100100011001000110010000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000110010001100100,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000001100100,
                    0b01100100000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b01100100011001000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000011001000110010000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000110010001100100,
                    0b01100100011001000110010001100100,
                    0b01100100011001000110010001100100,
                    0b01100100011001000110010001100100,
                    0b01100100011001000110010001100100
                ],
                [
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000011001000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000110010000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000001100100,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b01100100011001000110010001100100,
                    0b01100100011001000110010001100100,
                    0b01100100011001000110010001100100,
                    0b01100100011001000110010001100100,
                    0b01100100000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000011001000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000110010000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000001100100,
                    0b00000000000000000000000000000000,
                    0b00000000000000000000000000000000
                ]
            ]
        )