from microbit import *

animation = [...]

def plot(x, y, value):
    # Insert your code here

def clear():
    # Insert your code here

while True:
    if button_a.is_pressed():
        if width * height % 32 == 0:
            for i in range(len(animation)):
                bits = []
                for j in range(width * height / 32):
                    for k in range(32):
                        bits.append(animation[i][j] >> 31 - k & 1)
                for j in range(width * height):
                    x = j % width
                    y = j // width
                    plot(x, y, bits[j])
                sleep(1000 / fps)
                clear()
        else:
            for i in range(len(animation)):
                bits = []
                for j in range(width * height % 32):
                    bits.append(animation[i][0] >> width * height % 32 - 1 - j & 1)
                for j in range((width * height - width * height % 32) / 32):
                    for k in range(32):
                        bits.append(animation[i][j + 1] >> 31 - k & 1)
                for j in range(width * height):
                    x = j % width
                    y = j // width
                    plot(x, y, bits[j])
                sleep(1000 / fps)
                clear()