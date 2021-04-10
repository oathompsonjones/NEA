from microbit import *

animation = [...]

def plot(x, y, r, g, b):
    # Insert your code here

def clear():
    # Insert your code here

while True:
    if button_a.is_pressed():
        for i in range(len(animation)):
            bits = []
            for j in range(width * height):
                bits.append([])
                bits[j].append(animation[i][j] >> 16 & 255)
                bits[j].append(animation[i][j] >> 8 & 255)
                bits[j].append(animation[i][j] & 255)
            for j in range(width * height):
                x = j % width
                y = j // width
                plot(x, y, bits[j][0], bits[j][1], bits[j][2])
            sleep(1000 / fps)
            clear()