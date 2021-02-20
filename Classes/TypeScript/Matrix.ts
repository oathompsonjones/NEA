class Matrix {
    public static Identity(size: number): Matrix {
        const M = this.create0Array(size, size);
        for (let i = 0; i < size; ++i)
            for (let j = 0; j < size; ++j)
                M[i][j] = i === j ? 1 : 0;
        return new Matrix(M);
    }

    public static Cofactors(size: number): number[][] {
        const M = this.create0Array(size, size);
        for (let i = 0; i < size; ++i)
            for (let j = 0; j < size; ++j)
                M[i][j] = Math.pow(-1, i + j);
        return M;
    }

    /*
    This was needed because JS is weird.
    A Shorthand way to produce a filled in 2D array would be const array = Array(height).fill(Array(width).fill(0)).
    This causes some very strange behaviour when changing values, however.
    For example, when doing array[0][0] = 1, the expected result would be to change the first value of the first row from 0 to 1.
    In actual fact, that changes the first value of EVERY row from 0 to 1.
    I don't know why this happens, but it does.
    */
    public static create0Array(width: number, height: number): number[][] {
        const A: number[][] = [];
        for (let i = 0; i < height; ++i) {
            A[i] = [];
            for (let j = 0; j < width; ++j) {
                A[i][j] = 0;
            }
        }
        return A;
    }

    private _width: number;
    private _height: number;

    constructor(private data: number[][]) {
        if (!data.map((row) => row.length).every((x) => x === data[0].length)) throw new Error("That is not a valid matrix.");
        this._width = data[0].length;
        this._height = data.length;
    }

    public get width(): number {
        return this._width;
    }

    public get height(): number {
        return this._height;
    }

    public get square(): boolean {
        return this._width === this._height;
    }

    public get value(): number[][] {
        return this.data;
    }

    public get reversedRows(): Matrix {
        const rawData: number[][] = this.value;
        const newData: number[][] = Matrix.create0Array(this.width, this.height);
        for (let i = 0; i < this.width; ++i)
            for (let j = 0; j < this.height; ++j)
                newData[j][i] = rawData[j][this.width - 1 - i];
        return new Matrix(newData);
    }

    public get reversedColumns(): Matrix {
        const rawData: number[][] = this.value;
        const newData: number[][] = Matrix.create0Array(this.width, this.height);
        for (let i = 0; i < this.width; ++i)
            for (let j = 0; j < this.height; ++j)
                newData[j][i] = rawData[this.height - 1 - j][i];
        return new Matrix(newData);
    }

    public get transposition(): Matrix {
        if (!this.square) throw new Error("Non-square matrices do not have transpositions.");
        const M = Matrix.create0Array(this.width, this.height);
        for (let i = 0; i < this.width; i++)
            for (let j = 0; j < this.height; j++)
                M[j][i] = this.data[i][j];
        return new Matrix(M);
    }

    public getMinor(row: number, column: number): number {
        // Remove ith row and jth column, then get determinant.
        const A = Matrix.create0Array(this.width - 1, this.height - 1);
        for (let i = 0; i < this.width - 1; ++i)
            for (let j = 0; j < this.height - 1; ++j)
                A[i][j] = this.data[i < row ? i : i + 1][j < column ? j : j + 1];
        return new Matrix(A).determinant;
    }

    public get determinant(): number {
        if (!this.square) throw new Error("Non-square matrices do not have determinants.");
        if (this.width === 2) return this.data[0][0] * this.data[1][1] - this.data[0][1] * this.data[1][0];
        const CM = Matrix.create0Array(this.width, this.height);
        for (let i = 0; i < this.width; ++i)
            for (let j = 0; j < this.height; ++j)
                CM[i][j] = Matrix.Cofactors(this.width)[i][j] * this.getMinor(i, j);
        let det = 0;
        CM[0].forEach((x, i) => det += x * this.data[0][i]);
        return det;
    }

    public get adjoint(): Matrix {
        if (!this.square) throw new Error("Non-square matrices do not have adjoints.");
        const T = this.transposition;
        const A = T.value;
        for (let i = 0; i < this.width; ++i)
            for (let j = 0; j < this.height; ++j)
                A[i][j] = Matrix.Cofactors(this.width)[i][j] * T.getMinor(i, j);
        return new Matrix(A);
    }

    public get inverse(): Matrix {
        if (!this.square) throw new Error("Non-square matrices do not have inverses.");
        if (this.determinant === 0) throw new Error("This matrix has a determinant of 0, so does not have an inverse.");
        // 1 / det * adjoint
        const D = this.determinant;
        const I = this.adjoint.value;
        for (let i = 0; i < this.width; ++i)
            for (let j = 0; j < this.height; ++j)
                I[i][j] *= 1 / D;
        return new Matrix(I);
    }

    public multiply(right: Matrix | number): Matrix {
        if (typeof right === "number") {
            const M = Matrix.create0Array(this.width, this.height);
            for (let i = 0; i < this.width; ++i)
                for (let j = 0; j < this.height; ++j)
                    M[i][j] = this.value[i][j] * right;
            return new Matrix(M);
        }
        right = right as Matrix;
        if (this.width !== right.height) throw new Error("These matrices cannot be multiplied.");
        const M = right.width === 1
            ? Matrix.create0Array(right.width, right.height)
            : Matrix.create0Array(this.height, right.width);
        for (let i = 0; i < this.height; ++i)
            for (let j = 0; j < right.width; ++j)
                for (let k = 0; k < this.width; ++k)
                    M[i][j] += this.data[i][k] * right.data[k][j];
        return new Matrix(M);
    }

    public rotate(angle: number = 90): Matrix {
        if (!this.square) throw new Error("Cannot rotate non-square matrices.");
        switch (angle) {
            case 90:
                return this.transposition.reversedRows;
            case 180:
                return this.reversedRows.reversedColumns;
            case 270:
                return this.transposition.reversedColumns;
            default:
                throw new Error("The angle must be 90, 180 or 270 degrees.");
        }
    }

    public translate(direction: "left" | "right" | "up" | "down"): Matrix {
        if (!this.square) throw new Error("Cannot translate non-square matrices.");
        const shiftMatrix: number[][] = Matrix.create0Array(this.width, this.height);
        if (direction === "left" || direction === "down") {
            for (let i = 0; i < this.width; ++i)
                for (let j = 0; j < this.height; ++j)
                    if (i === j - 1) shiftMatrix[j][i] = 1;
            
            if (direction === "left") return this.multiply(new Matrix(shiftMatrix));
            else return new Matrix(shiftMatrix).multiply(this);
        } else {
            for (let i = 0; i < this.width; ++i)
                for (let j = 0; j < this.height; ++j)
                    if (i === j + 1) shiftMatrix[j][i] = 1;
            
            if (direction === "right") return this.multiply(new Matrix(shiftMatrix));
            else return new Matrix(shiftMatrix).multiply(this);
        }
    }
}