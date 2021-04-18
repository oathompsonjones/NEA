"use strict";
class Matrix {
    data;
    static Identity(size) {
        const M = this.create0Array(size, size);
        for (let i = 0; i < size; ++i)
            for (let j = 0; j < size; ++j)
                M[i][j] = i === j ? 1 : 0;
        return new Matrix(M);
    }
    static Cofactors(size) {
        const M = this.create0Array(size, size);
        for (let i = 0; i < size; ++i)
            for (let j = 0; j < size; ++j)
                M[i][j] = Math.pow(-1, i + j);
        return M;
    }
    static create0Array(width, height) {
        const A = [];
        for (let i = 0; i < height; ++i) {
            A[i] = [];
            for (let j = 0; j < width; ++j) {
                A[i][j] = 0;
            }
        }
        return A;
    }
    _width;
    _height;
    constructor(data) {
        this.data = data;
        if (!data.map((row) => row.length).every((x) => x === data[0].length))
            throw new Error("That is not a valid matrix.");
        this._width = data[0].length;
        this._height = data.length;
    }
    get width() {
        return this._width;
    }
    get height() {
        return this._height;
    }
    get square() {
        return this._width === this._height;
    }
    get value() {
        return this.data;
    }
    get reversedRows() {
        const rawData = this.value;
        const newData = Matrix.create0Array(this.width, this.height);
        for (let i = 0; i < this.width; ++i)
            for (let j = 0; j < this.height; ++j)
                newData[j][i] = rawData[j][this.width - 1 - i];
        return new Matrix(newData);
    }
    get reversedColumns() {
        const rawData = this.value;
        const newData = Matrix.create0Array(this.width, this.height);
        for (let i = 0; i < this.width; ++i)
            for (let j = 0; j < this.height; ++j)
                newData[j][i] = rawData[this.height - 1 - j][i];
        return new Matrix(newData);
    }
    get transposition() {
        if (!this.square)
            throw new Error("Non-square matrices do not have transpositions.");
        const M = Matrix.create0Array(this.width, this.height);
        for (let i = 0; i < this.width; i++)
            for (let j = 0; j < this.height; j++)
                M[j][i] = this.data[i][j];
        return new Matrix(M);
    }
    getMinor(row, column) {
        const A = Matrix.create0Array(this.width - 1, this.height - 1);
        for (let i = 0; i < this.width - 1; ++i)
            for (let j = 0; j < this.height - 1; ++j)
                A[i][j] = this.data[i < row ? i : i + 1][j < column ? j : j + 1];
        return new Matrix(A).determinant;
    }
    get determinant() {
        if (!this.square)
            throw new Error("Non-square matrices do not have determinants.");
        if (this.width === 2)
            return this.data[0][0] * this.data[1][1] - this.data[0][1] * this.data[1][0];
        const CM = Matrix.create0Array(this.width, this.height);
        for (let i = 0; i < this.width; ++i)
            for (let j = 0; j < this.height; ++j)
                CM[i][j] = Matrix.Cofactors(this.width)[i][j] * this.getMinor(i, j);
        let det = 0;
        CM[0].forEach((x, i) => det += x * this.data[0][i]);
        return det;
    }
    get adjugate() {
        if (!this.square)
            throw new Error("Non-square matrices do not have adjugates.");
        const T = this.transposition;
        const A = T.value;
        for (let i = 0; i < this.width; ++i)
            for (let j = 0; j < this.height; ++j)
                A[i][j] = Matrix.Cofactors(this.width)[i][j] * T.getMinor(i, j);
        return new Matrix(A);
    }
    get inverse() {
        if (!this.square)
            throw new Error("Non-square matrices do not have inverses.");
        if (this.determinant === 0)
            throw new Error("This matrix has a determinant of 0, so does not have an inverse.");
        const D = this.determinant;
        const I = this.adjugate.value;
        for (let i = 0; i < this.width; ++i)
            for (let j = 0; j < this.height; ++j)
                I[i][j] *= 1 / D;
        return new Matrix(I);
    }
    multiply(right) {
        if (typeof right === "number") {
            const M = Matrix.create0Array(this.width, this.height);
            for (let i = 0; i < this.width; ++i)
                for (let j = 0; j < this.height; ++j)
                    M[i][j] = this.value[i][j] * right;
            return new Matrix(M);
        }
        right = right;
        if (this.width !== right.height)
            throw new Error("These matrices cannot be multiplied.");
        const M = right.width === 1
            ? Matrix.create0Array(right.width, right.height)
            : Matrix.create0Array(this.height, right.width);
        for (let i = 0; i < this.height; ++i)
            for (let j = 0; j < right.width; ++j)
                for (let k = 0; k < this.width; ++k)
                    M[i][j] += this.data[i][k] * right.data[k][j];
        return new Matrix(M);
    }
    rotate(angle = 90) {
        if (!this.square)
            throw new Error("Cannot rotate non-square matrices.");
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
    translate(direction) {
        if (!this.square)
            throw new Error("Cannot translate non-square matrices.");
        const shiftMatrix = Matrix.create0Array(this.width, this.height);
        if (direction === "left" || direction === "down") {
            for (let i = 0; i < this.width; ++i)
                for (let j = 0; j < this.height; ++j)
                    if (i === j - 1)
                        shiftMatrix[j][i] = 1;
            if (direction === "left")
                return this.multiply(new Matrix(shiftMatrix));
            else
                return new Matrix(shiftMatrix).multiply(this);
        }
        else {
            for (let i = 0; i < this.width; ++i)
                for (let j = 0; j < this.height; ++j)
                    if (i === j + 1)
                        shiftMatrix[j][i] = 1;
            if (direction === "right")
                return this.multiply(new Matrix(shiftMatrix));
            else
                return new Matrix(shiftMatrix).multiply(this);
        }
    }
}
