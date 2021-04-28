/**
 * @description Class representing a mathematical matrix.
 * @class Matrix
 */
class Matrix {
    /**
     * @description Gets the nxn identity matrix.
     * @static
     * @param {number} size The width and height of the square matrix.
     * @return {*}  {Matrix}
     * @memberof Matrix
     */
    public static Identity(size: number): Matrix {
        const M = this.create0Array(size, size);
        for (let i = 0; i < size; ++i)
            for (let j = 0; j < size; ++j)
                M[i][j] = i === j ? 1 : 0;
        return new Matrix(M);
    }

    /**
     * @description Gets the positions of the negative and positive cofactors of an nxn matrix.
     * @static
     * @param {number} size The width and height of the square matrix.
     * @return {*}  {number[][]}
     * @memberof Matrix
     */
    public static Cofactors(size: number): number[][] {
        const M = this.create0Array(size, size);
        for (let i = 0; i < size; ++i)
            for (let j = 0; j < size; ++j)
                M[i][j] = Math.pow(-1, i + j);
        return M;
    }

    /**
     * @description Creates a 2D array and fills it with zeros, can then be used as the data for a Matrix object.
     * This was needed because JS is weird and wonderful.
     * A Shorthand way to produce a filled in 2D array would be ```const array = Array(height).fill(Array(width).fill(0))```
     * However, this causes some very strange behaviour when changing values.
     * For example, when doing `array[0][0] = 1`, the expected result would be to change the first value of the first row from 0 to 1.
     * In actual fact, that changes the first value of EVERY row from 0 to 1.
     * @static
     * @param {number} width The width of the matrix.
     * @param {number} height The height of the matrix.
     * @return {*}  {number[][]}
     * @memberof Matrix
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

    /**
     * @description Stores the width of the matrix.
     * @private
     * @type {number}
     * @memberof Matrix
     */
    private _width: number;
    /**
     * @description Stores the height of the matrix.
     * @private
     * @type {number}
     * @memberof Matrix
     */
    private _height: number;

    /**
     * Creates an instance of Matrix.
     * @param {number[][]} data A 2D array containing the data to fill the matrix.
     * @memberof Matrix
     */
    constructor(private data: number[][]) {
        if (!data.map((row) => row.length).every((x) => x === data[0].length)) throw new Error("That is not a valid matrix.");
        this._width = data[0].length;
        this._height = data.length;
    }

    /**
     * @description Gets the width of the matrix.
     * @readonly
     * @type {number}
     * @memberof Matrix
     */
    public get width(): number {
        return this._width;
    }

    /**
     * @description Gets the height of the matrix.
     * @readonly
     * @type {number}
     * @memberof Matrix
     */
    public get height(): number {
        return this._height;
    }

    /**
     * @description Shows if the matrix is aa square matrix.
     * @readonly
     * @type {boolean}
     * @memberof Matrix
     */
    public get square(): boolean {
        return this._width === this._height;
    }

    /**
     * @description Gets the raw data of the matrix (a 2D array).
     * @readonly
     * @type {number[][]}
     * @memberof Matrix
     */
    public get value(): number[][] {
        return this.data;
    }

    /**
     * @description Gets a new matrix with the rows reversed.
     * @readonly
     * @type {Matrix}
     * @memberof Matrix
     */
    public get reversedRows(): Matrix {
        const rawData: number[][] = this.value;
        const newData: number[][] = Matrix.create0Array(this.width, this.height);
        for (let i = 0; i < this.width; ++i)
            for (let j = 0; j < this.height; ++j)
                newData[j][i] = rawData[j][this.width - 1 - i];
        return new Matrix(newData);
    }

    /**
     * @description Gets a new matrix with the columns reversed.
     * @readonly
     * @type {Matrix}
     * @memberof Matrix
     */
    public get reversedColumns(): Matrix {
        const rawData: number[][] = this.value;
        const newData: number[][] = Matrix.create0Array(this.width, this.height);
        for (let i = 0; i < this.width; ++i)
            for (let j = 0; j < this.height; ++j)
                newData[j][i] = rawData[this.height - 1 - j][i];
        return new Matrix(newData);
    }

    /**
     * @description Gets the transposition of the matrix.
     * @readonly
     * @type {Matrix}
     * @memberof Matrix
     */
    public get transposition(): Matrix {
        if (!this.square) throw new Error("Non-square matrices do not have transpositions.");
        const M = Matrix.create0Array(this.width, this.height);
        for (let i = 0; i < this.width; i++)
            for (let j = 0; j < this.height; j++)
                M[j][i] = this.data[i][j];
        return new Matrix(M);
    }

    /**
     * @description Gets the matrix minor for a given row and column.
     * @param {number} row The row to get the minor for.
     * @param {number} column The column to get the minor for.
     * @return {*}  {number}
     * @memberof Matrix
     */
    public getMinor(row: number, column: number): number {
        // Remove ith row and jth column, then get determinant.
        const A = Matrix.create0Array(this.width - 1, this.height - 1);
        for (let i = 0; i < this.width - 1; ++i)
            for (let j = 0; j < this.height - 1; ++j)
                A[i][j] = this.data[i < row ? i : i + 1][j < column ? j : j + 1];
        return new Matrix(A).determinant;
    }

    /**
     * @description Gets the determinant of the matrix.
     * @readonly
     * @type {number}
     * @memberof Matrix
     */
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

    /**
     * @description Gets the adjugate of the matrix.
     * @readonly
     * @type {Matrix}
     * @memberof Matrix
     */
    public get adjugate(): Matrix {
        if (!this.square) throw new Error("Non-square matrices do not have adjugates.");
        const T = this.transposition;
        const A = T.value;
        for (let i = 0; i < this.width; ++i)
            for (let j = 0; j < this.height; ++j)
                A[i][j] = Matrix.Cofactors(this.width)[i][j] * T.getMinor(i, j);
        return new Matrix(A);
    }

    /**
     * @description Gets the inverse of the matrix.
     * @readonly
     * @type {Matrix}
     * @memberof Matrix
     */
    public get inverse(): Matrix {
        if (!this.square) throw new Error("Non-square matrices do not have inverses.");
        if (this.determinant === 0) throw new Error("This matrix has a determinant of 0, so does not have an inverse.");
        // 1 / det * adjugate
        const D = this.determinant;
        const I = this.adjugate.value;
        for (let i = 0; i < this.width; ++i)
            for (let j = 0; j < this.height; ++j)
                I[i][j] *= 1 / D;
        return new Matrix(I);
    }

    /**
     * @description Post multiplies the matrix by a new given matrix or number.
     * @param {(Matrix | number)} right The matrix or number to post multiply by.
     * @return {*}  {Matrix}
     * @memberof Matrix
     */
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

    /**
     * @description Rotates the matrix by the given number of degrees.
     * @param {(90 | 180 | 270)} [angle=90] The number of degrees to rotate by.
     * @return {*}  {Matrix}
     * @memberof Matrix
     */
    public rotate(angle: 90 | 180 | 270 = 90): Matrix {
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

    /**
     * @description Shifts the matrix one position in the given direction.
     * @param {("left" | "right" | "up" | "down")} direction The direction to shift the matrix.
     * @return {*}  {Matrix}
     * @memberof Matrix
     */
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