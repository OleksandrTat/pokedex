1. Contexto: ¿Qué es una inyección SQL?
Es un ataque donde un atacante inserta código SQL malicioso en una consulta, aprovechando entradas de usuario no validadas. Por ejemplo:

------------------------------------------------------------
-- Si un usuario ingresa: ' OR '1'='1
SELECT * FROM usuarios WHERE nombre = '$input';
-- La consulta resultante sería:
SELECT * FROM usuarios WHERE nombre = '' OR '1'='1'; -- Acceso no autorizado
------------------------------------------------------------

2. ¿Cómo funcionan las consultas preparadas?
Las consultas preparadas dividen la ejecución de SQL en dos etapas:

a)Preparación: Se define la estructura de la consulta con marcadores de posición (?).

b)Ejecución: Se asignan valores a los marcadores, tratándolos como datos, no como parte del código SQL.

Ejemplo:
------------------------------------------------------------
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE nombre = ?");
$stmt->bind_param("s", $input); // "s" indica que es una cadena (string)
$stmt->execute();
------------------------------------------------------------

3. Rol de bind_param en la seguridad
a) Separación de código y datos
bind_param vincula los valores a los marcadores de posición, asegurando que los datos del usuario nunca se interpreten como código SQL.

La base de datos comprende la estructura de la consulta antes de recibir los datos, evitando que se altere su lógica.

b) Escapado automático
Los valores se envían a la base de datos en un formato seguro, escapando caracteres especiales (como ', ", #, etc.).

Ejemplo:

------------------------------------------------------------
$input = "admin'--";
$stmt->bind_param("s", $input); // Se convierte en "admin\'--"
------------------------------------------------------------

La consulta resultante será:

------------------------------------------------------------
SELECT * FROM usuarios WHERE nombre = 'admin\'--' -- El comentario (--) no se ejecuta
------------------------------------------------------------

c) Tipado estricto
bind_param requiere especificar el tipo de dato de cada parámetro (s = string, i = entero, etc.).

Esto evita errores de interpretación y asegura que los datos se manejen correctamente.

------------------------------------------------------------
$stmt->bind_param("si", $nombre, $edad); // String e integer
------------------------------------------------------------

4. ¿Por qué es más seguro que métodos como mysqli_real_escape_string?
Escapado manual: Métodos como mysqli_real_escape_string dependen de que el desarrollador recuerde aplicarlos en cada entrada, lo cual es propenso a errores.

Consultas preparadas: La lógica de seguridad está integrada directamente en el flujo de trabajo, reduciendo riesgos humanos.

5. Casos donde bind_param no es suficiente
Aunque bind_param es eficaz para valores, hay escenarios que requieren precauciones adicionales:

Identificadores dinámicos (nombres de tablas/columnas):

------------------------------------------------------------
// ¡No se pueden usar marcadores para nombres de columnas!
$columna = $_GET['columna'];
$stmt = $conn->prepare("SELECT ? FROM usuarios");
$stmt->bind_param("s", $columna); // Esto insertará un string, no el nombre de la columna.
Solución: Validar contra una lista permitida.

------------------------------------------------------------
$columnas_permitidas = ['nombre', 'email'];
if (!in_array($columna, $columnas_permitidas)) {
    die("Columna no válida");
}
6. Beneficios adicionales
a)Rendimiento: Si una consulta se ejecuta múltiples veces con distintos valores, la base de datos reutiliza el plan de ejecución.

b)Claridad del código: Mejora la legibilidad al separar claramente la consulta de los datos.

CONCLUSIÓN
bind_param es una piedra angular en la seguridad de aplicaciones PHP al:

a)Separar código SQL de datos de usuario.

b)Escapar automáticamente caracteres peligrosos.

c)Reforzar el tipado de datos.

Su uso, junto con consultas preparadas, es una práctica obligatoria para prevenir inyecciones SQL, garantizando que los datos externos se traten siempre como valores literales, no como código ejecutable.