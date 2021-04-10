#include <Adafruit_GFX.h>
#include <Adafruit_NeoMatrix.h>
#include <Adafruit_NeoPixel.h>

// Use digital pin 8 as your data pin.
Adafruit_NeoMatrix matrix = Adafruit_NeoMatrix(8, 8, 8, NEO_MATRIX_TOP + NEO_MATRIX_LEFT + NEO_MATRIX_ROWS + NEO_MATRIX_PROGRESSIVE);

const long animation[9][64] = {
    {0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010},
    {0b111111111001001100000000,
     0b000000001111110111111111,
     0b100101000010000110010010,
     0b100100011001000110010001,
     0b111111111111101100000000,
     0b000001000011001111111111,
     0b101010100111100101000010,
     0b111111110010011000000000,
     0b111111111111101100000000,
     0b000001000011001111111111,
     0b101010100111100101000010,
     0b111111110010011000000000,
     0b000000001111100100000000,
     0b111111110100000011111111,
     0b111111111111111111111111,
     0b111111111001001100000000,
     0b000000001111100100000000,
     0b111111110100000011111111,
     0b111111111111111111111111,
     0b111111111001001100000000,
     0b000000001111110111111111,
     0b100101000010000110010010,
     0b100100011001000110010001,
     0b111111111111101100000000,
     0b000000001111110111111111,
     0b100101000010000110010010,
     0b100100011001000110010001,
     0b111111111111101100000000,
     0b000001000011001111111111,
     0b101010100111100101000010,
     0b111111110010011000000000,
     0b000000001111100100000000,
     0b000001000011001111111111,
     0b101010100111100101000010,
     0b111111110010011000000000,
     0b000000001111100100000000,
     0b111111110100000011111111,
     0b111111111111111111111111,
     0b111111111001001100000000,
     0b000000001111110111111111,
     0b111111110100000011111111,
     0b111111111111111111111111,
     0b111111111001001100000000,
     0b000000001111110111111111,
     0b100101000010000110010010,
     0b100100011001000110010001,
     0b111111111111101100000000,
     0b000001000011001111111111,
     0b100101000010000110010010,
     0b100100011001000110010001,
     0b111111111111101100000000,
     0b000001000011001111111111,
     0b101010100111100101000010,
     0b111111110010011000000000,
     0b000000001111100100000000,
     0b111111110100000011111111,
     0b101010100111100101000010,
     0b111111110010011000000000,
     0b000000001111100100000000,
     0b111111110100000011111111,
     0b111111111111111111111111,
     0b111111111001001100000000,
     0b000000001111110111111111,
     0b100101000010000110010010},
    {0b101010100111100101000010,
     0b100101000010000110010010,
     0b111111110100000011111111,
     0b000001000011001111111111,
     0b000000001111110111111111,
     0b000000001111100100000000,
     0b111111111111101100000000,
     0b111111111001001100000000,
     0b111111110010011000000000,
     0b100100011001000110010001,
     0b111111111111111111111111,
     0b101010100111100101000010,
     0b100101000010000110010010,
     0b111111110100000011111111,
     0b000001000011001111111111,
     0b000000001111110111111111,
     0b000000001111100100000000,
     0b111111111111101100000000,
     0b111111111001001100000000,
     0b111111110010011000000000,
     0b100100011001000110010001,
     0b111111111111111111111111,
     0b101010100111100101000010,
     0b100101000010000110010010,
     0b111111110100000011111111,
     0b000001000011001111111111,
     0b000000001111110111111111,
     0b000000001111100100000000,
     0b111111111111101100000000,
     0b111111111001001100000000,
     0b111111110010011000000000,
     0b100100011001000110010001,
     0b111111111111111111111111,
     0b101010100111100101000010,
     0b100101000010000110010010,
     0b111111110100000011111111,
     0b000001000011001111111111,
     0b000000001111110111111111,
     0b000000001111100100000000,
     0b111111111111101100000000,
     0b111111111001001100000000,
     0b111111110010011000000000,
     0b100100011001000110010001,
     0b111111111111111111111111,
     0b101010100111100101000010,
     0b100101000010000110010010,
     0b111111110100000011111111,
     0b000001000011001111111111,
     0b000000001111110111111111,
     0b000000001111100100000000,
     0b111111111111101100000000,
     0b111111111001001100000000,
     0b111111110010011000000000,
     0b100100011001000110010001,
     0b111111111111111111111111,
     0b101010100111100101000010,
     0b100101000010000110010010,
     0b111111110100000011111111,
     0b000001000011001111111111,
     0b000000001111110111111111,
     0b000000001111100100000000,
     0b111111111111101100000000,
     0b111111111001001100000000,
     0b111111110010011000000000},
    {0b100101000010000110010010,
     0b000000001111110111111111,
     0b111111111001001100000000,
     0b111111111111111111111111,
     0b111111110100000011111111,
     0b000000001111100100000000,
     0b111111110010011000000000,
     0b101010100111100101000010,
     0b111111110100000011111111,
     0b000000001111100100000000,
     0b111111110010011000000000,
     0b101010100111100101000010,
     0b000001000011001111111111,
     0b111111111111101100000000,
     0b100100011001000110010001,
     0b100101000010000110010010,
     0b000001000011001111111111,
     0b111111111111101100000000,
     0b100100011001000110010001,
     0b100101000010000110010010,
     0b000000001111110111111111,
     0b111111111001001100000000,
     0b111111111111111111111111,
     0b111111110100000011111111,
     0b000000001111110111111111,
     0b111111111001001100000000,
     0b111111111111111111111111,
     0b111111110100000011111111,
     0b000000001111100100000000,
     0b111111110010011000000000,
     0b101010100111100101000010,
     0b000001000011001111111111,
     0b000000001111100100000000,
     0b111111110010011000000000,
     0b101010100111100101000010,
     0b000001000011001111111111,
     0b111111111111101100000000,
     0b100100011001000110010001,
     0b100101000010000110010010,
     0b000000001111110111111111,
     0b111111111111101100000000,
     0b100100011001000110010001,
     0b100101000010000110010010,
     0b000000001111110111111111,
     0b111111111001001100000000,
     0b111111111111111111111111,
     0b111111110100000011111111,
     0b000000001111100100000000,
     0b111111111001001100000000,
     0b111111111111111111111111,
     0b111111110100000011111111,
     0b000000001111100100000000,
     0b111111110010011000000000,
     0b101010100111100101000010,
     0b000001000011001111111111,
     0b111111111111101100000000,
     0b111111110010011000000000,
     0b101010100111100101000010,
     0b000001000011001111111111,
     0b111111111111101100000000,
     0b100100011001000110010001,
     0b100101000010000110010010,
     0b000000001111110111111111,
     0b111111111001001100000000},
    {0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010},
    {0b100101000010000110010010,
     0b111111110100000011111111,
     0b000001000011001111111111,
     0b000000001111110111111111,
     0b000000001111100100000000,
     0b111111111111101100000000,
     0b111111111001001100000000,
     0b111111110010011000000000,
     0b000000001111110111111111,
     0b000000001111100100000000,
     0b111111111111101100000000,
     0b111111111001001100000000,
     0b111111110010011000000000,
     0b100100011001000110010001,
     0b111111111111111111111111,
     0b101010100111100101000010,
     0b111111111001001100000000,
     0b111111110010011000000000,
     0b100100011001000110010001,
     0b111111111111111111111111,
     0b101010100111100101000010,
     0b100101000010000110010010,
     0b111111110100000011111111,
     0b000001000011001111111111,
     0b111111111111111111111111,
     0b101010100111100101000010,
     0b100101000010000110010010,
     0b111111110100000011111111,
     0b000001000011001111111111,
     0b000000001111110111111111,
     0b000000001111100100000000,
     0b111111111111101100000000,
     0b111111110100000011111111,
     0b000001000011001111111111,
     0b000000001111110111111111,
     0b000000001111100100000000,
     0b111111111111101100000000,
     0b111111111001001100000000,
     0b111111110010011000000000,
     0b100100011001000110010001,
     0b000000001111100100000000,
     0b111111111111101100000000,
     0b111111111001001100000000,
     0b111111110010011000000000,
     0b100100011001000110010001,
     0b111111111111111111111111,
     0b101010100111100101000010,
     0b100101000010000110010010,
     0b111111110010011000000000,
     0b100100011001000110010001,
     0b111111111111111111111111,
     0b101010100111100101000010,
     0b100101000010000110010010,
     0b111111110100000011111111,
     0b000001000011001111111111,
     0b000000001111110111111111,
     0b101010100111100101000010,
     0b100101000010000110010010,
     0b111111110100000011111111,
     0b000001000011001111111111,
     0b000000001111110111111111,
     0b000000001111100100000000,
     0b111111111111101100000000,
     0b111111111001001100000000},
    {0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010},
    {0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010},
    {0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010,
     0b111111111111111111111111,
     0b100100011001000110010001,
     0b111111110010011000000000,
     0b111111111001001100000000,
     0b111111111111101100000000,
     0b000000001111100100000000,
     0b000000001111110111111111,
     0b000001000011001111111111,
     0b111111110100000011111111,
     0b100101000010000110010010,
     0b101010100111100101000010}};

void plot(int x, int y, int r, int g, int b)
{
    matrix.drawPixel(x, y, matrix.Color(r, g, b));
}

void clearScreen()
{
    matrix.fillScreen(0);
}

void setup()
{
    matrix.begin();
    matrix.setBrightness(10);
}

void loop()
{
    for (int i = 0; i < 9; ++i)
    {
        int bits[64][3];
        for (int j = 0; j < 64; ++j)
        {
            bits[j][0] = animation[i][j] >> 16 & 255;
            bits[j][1] = animation[i][j] >> 8 & 255;
            bits[j][2] = animation[i][j] & 255;
        }
        for (int j = 0; j < 64; ++j)
        {
            int x = j % 8;
            int y = j / 8;
            plot(x, y, bits[j][0], bits[j][1], bits[j][2]);
        }
        matrix.show();
        delay(1000 / 5);
        clearScreen();
    }
}