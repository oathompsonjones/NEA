const long animation[FRAME_COUNT][FRAME_LENGTH] = {...};

void plot(int x, int y, int value)
{
    // Insert your code here
}

void clear()
{
    // Insert your code here
}

int main()
{
    while (true)
    {
        if (width * height % 32 == 0)
        {
            for (int i = 0; i < ANIMATION_LENGTH; ++i)
            {
                int bits[width * height];
                for (int j = 0; j < width * height / 32; ++j)
                    for (int k = 0; k < 32; ++k)
                        bits[j * 32 + k] = animation[i][j] >> 31 - k & 1;
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
                for (int j = 0; j < width * height % 32; ++j)
                    bits[j] = animation[i][0] >> width * height % 32 - 1 - j & 1;
                for (int j = 0; j < (width * height - width * height % 32) / 32; ++j)
                    for (int k = 0; k < 32; ++k)
                        bits[width * height % 32 + j * 32 + k] = animation[i][j + 1] >> 31 - k & 1;
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
    return 0;
}