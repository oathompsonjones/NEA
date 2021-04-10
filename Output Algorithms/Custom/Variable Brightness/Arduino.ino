const long animation[ANIMATION_LENGTH][FRAME_LENGTH] = {...};

void plot(int x, int y, int value)
{
    // Insert your code here
}

void clear()
{
    // Insert your code here
}

void setup()
{
    // Insert your code here
}

void loop()
{
    if (width * height % 4 == 0)
    {
        for (int i = 0; i < ANIMATION_LENGTH; ++i)
        {
            int bits[width * height];
            for (int j = 0; j < width * height / 4; ++j)
                for (int k = 0; k < 4; ++k)
                    bits[j * 4 + k] = animation[i][j] >> (3 - k) * 8 & 255;
            for (int j = 0; j < width * height; ++j)
            {
                int x = j % width;
                int y = j / width;
                plot(x, y, bits[j]);
            }
            delay(1000 / fps);
            clear();
        }
    }
    else
    {
        for (int i = 0; i < ANIMATION_LENGTH; ++i)
        {
            int bits[width * height];
            for (int j = 0; j < width * height % 4; ++j)
                bits[j] = animation[i][0] >> width * height % 4 - 1 - j & 1;
            for (int j = 0; j < (width * height - width * height % 4) / 4; ++j)
                for (int k = 0; k < 4; ++k)
                    bits[width * height % 4 + j * 4 + k] = animation[i][j + 1] >> (3 - k) * 8 & 255;
            for (int j = 0; j < width * height; ++j)
            {
                int x = j % width;
                int y = j / width;
                plot(x, y, bits[j]);
            }
            delay(1000 / fps);
            clear();
        }
    }
}