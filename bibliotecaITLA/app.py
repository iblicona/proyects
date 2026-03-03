import sqlite3
from flask import Flask, render_template, request

app = Flask(__name__)

@app.route("/")
def inicio():
    return render_template("inicio.html")

@app.route("/registro", methods=["POST"])
def registrar():
    nombre = request.form["nombre"]
    apellidos = request.form["apellidos"]
    matricula = request.form["matricula"]
    correo = request.form["correo"]
    tipo = request.form["tipo"]
    area = request.form["area"]

    try:
        conn = sqlite3.connect("biblioteca.db")
        cursor = conn.cursor()

        cursor.execute("""
            INSERT INTO usuarios (nombre, apellidos, matricula, correo, tipo, area)
            VALUES (?, ?, ?, ?, ?, ?)
        """, (nombre, apellidos, matricula, correo, tipo, area))

        conn.commit()
        conn.close()

        print("✔ Registro guardado:", matricula)
        return "Usuario registrado correctamente"

    except sqlite3.IntegrityError:
        return "❌ La matrícula ya está registrada"


if __name__ == "__main__":
    app.run(debug=True)
