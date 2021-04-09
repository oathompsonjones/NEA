#include <Charliplexing.h>

const long animation[3][4] = {
    {
        0b00111111111111111000000000000110, // 30 LEDs
        0b00000000000110000000000001100000, // 32 LEDs
        0b00000001100000000000011000000000, // 32 LEDs
        0b00011000000000000111111111111111  // 32 LEDs
    },
    {
        0b00100000000000010110000000011000, // 30 LEDs
        0b01100001100000000100100000000000, // 32 LEDs
        0b11000000000001001000000001100001, // 32 LEDs
        0b10000110000000011010000000000001  // 32 LEDs
    },
    {
        0b00000000110000000000001100000000, // 30 LEDs
        0b00001100000000000011000000111111, // 32 LEDs
        0b11111111000000110000000000001100, // 32 LEDs
        0b00000000001100000000000011000000  // 32 LEDs
    }};

void plot(int x, int y, int v)
{
    LedSign::Set(x, y, v);
}

void clearScreen()
{
    for (int i = 0; i < 14; ++i)
        for (int j = 0; j < 9; ++j)
            plot(i, j, 0);
}

void setup()
{
    LedSign::Init();
}

void loop()
{
    for (int i = 0; i < 3; ++i)
    {
        int bits[14 * 9];
        for (int j = 0; j < 30; ++j)
            bits[j] = animation[i][0] >> (29 - j) & 1;
        for (int j = 0; j < 3; ++j)
            for (int k = 0; k < 32; ++k)
                bits[30 + j * 32 + k] = animation[i][j + 1] >> (31 - k) & 1;
        for (int j = 0; j < 14 * 9; ++j)
        {
            int x = j % 14;
            int y = j / 14;
            plot(x, y, bits[j]);
        }
        delay(1000 / 1);
        clearScreen();
    }
}
