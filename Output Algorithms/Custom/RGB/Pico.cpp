const long animation[ANIMATION_LENGTH][FRAME_LENGTH] = {...};

void plot(int x, int y, int r, int g, int b)
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

int main()
{
    while (true)
    {
        for (int i = 0; i < ANIMATION_LENGTH; ++i)
        {
            int bits[width * height][3];
            for (int j = 0; j < width * height; ++j)
            {
                bits[j][0] = animation[i][j] >> 16 & 255;
                bits[j][1] = animation[i][j] >> 8 & 255;
                bits[j][2] = animation[i][j] & 255;
            }
            for (int j = 0; j < width * height; ++j)
            {
                int x = j % width;
                int y = j / width;
                plot(x, y, bits[j][0], bits[j][1], bits[j][2]);
            }
            delay(1000 / fps);
            clear();
        }
    }
    return 0;
}