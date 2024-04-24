CREATE DATABASE IF NOT EXISTS Tienda;
USE Tienda;

CREATE TABLE Usuario (
    IdUsuario INT AUTO_INCREMENT PRIMARY KEY,
    NombreUsuario VARCHAR(30) NOT NULL,
    ApellidoUsuario VARCHAR(30) NOT NULL,
    Email VARCHAR(30) NOT NULL UNIQUE,
    Contraseña VARCHAR(100) NOT NULL,
    UsuarioAdmin BOOLEAN NOT NULL,
    Direccion VARCHAR(100),
    Saldo FLOAT,
    Valoracion INT
);

CREATE TABLE Videojuego (
    IdJuego INT AUTO_INCREMENT PRIMARY KEY,
    NombreJuego VARCHAR(30) NOT NULL,
    Descripcion VARCHAR(30) NOT NULL,
    Imagen VARCHAR(30) NOT NULL,
    Precio FLOAT,
    FechaLanzamiento DATE,
    Stock INT
);

CREATE TABLE Plataforma (
    IdPlataforma INT AUTO_INCREMENT PRIMARY KEY,
    NombrePlataforma VARCHAR(100)
);

CREATE TABLE Marca (
    IdMarca INT AUTO_INCREMENT PRIMARY KEY
    NombreMarca VARCHAR(100)
);

CREATE TABLE Videojuego_Plataforma (
    IdJuego INT,
    IdPlataforma INT,
    FOREIGN KEY (IdJuego) REFERENCES Videojuego(IdJuego) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (IdPlataforma) REFERENCES Plataforma(IdPlataforma) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (IdJuego, IdPlataforma)
);

CREATE TABLE Marca_Plataforma (
    IdMarca INT,
    IdPlataforma INT,
    FOREIGN KEY (IdMarca) REFERENCES Marca(IdMarca) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (IdPlataforma) REFERENCES Plataforma(IdPlataforma) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (IdMarca, IdPlataforma)
);

CREATE TABLE CodigoDescuento (
    IdCodigoDescuento INT AUTO_INCREMENT PRIMARY KEY,
    CodigoDescuento VARCHAR(100)
);
