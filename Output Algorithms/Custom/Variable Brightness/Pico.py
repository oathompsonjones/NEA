import time

animation = [...]

def plot(x, y, value):
    # Insert your code here

def clear():
    # Insert your code here

while True:
    if width * height % 4 == 0:
        for i in range(len(animation)):
            bits = []
            for j in range(width * height):
                for k in range(4):
                    bits.append(animation[i][j] >> (3 - k) * 8 & 255)
            for j in range(width * height):
                x = j % width
                y = j // width
                plot(x, y, bits[j])
            time.sleep(1000 / fps)
            clear()
    else:
        for i in range(len(animation)):
            bits = []
            for j in range(width * height % 4):
                bits.append(animation[i][0] >> width * height % 4 - 1 - j & 1)
            for j in range((width * height - width * height % 4) / 4):
                for k in range(4):
                    bits.append(animation[i][j + 1] >> (3 - k) * 8 & 255)
            for j in range(width * height):
                x = j % width
                y = j // width
                plot(x, y, bits[j])
            time.sleep(1000 / fps)
            clear()