# Prestashop 1.5 Plugin

## Introducción

  NPS se integra fácilmente a Prestashop, otorgando la posibilidad de poder configurar su medio de pago en simples pasos. En sólo cuestión de minutos su carrito de compras quedará listo para comenzar a operar en línea.

## Consideraciones

Este paquete se encuentra certificado para la versión 1.5.0.15 a 1.5.6.2

## Modos de Integración

Para el manejo de transacciones financieras el servidor PSP soporta dos mecanismos de integración en Prestashop con el método 3 partes:

•	PayOnline_3p  
•	Authorize_3p / Capture 

El método Authorize, precisa de una captura posterior desde el panel de administrador.  
El método Authorize_3p/Capture_3p actúa como PayOnline_3p donde la autorización y captura se realizan en la misma transacción.


## Instalación

** Para realizar la siguiente configuración es requisito tener instalado PrestaShop: 

Junto con esta documentación usted podra descargar el módulo que se integrará a Prestashop.

1. Extraer el archivo NPS.Prestashop.1.5.0.15.-.1.5.6.2.Connector.v0.03.006.tar.gz

2. Renombrar la carpeta admin con el nombre de la carpeta admin que está en prestashop.

3. Copiar los cuatro directorios extraídas y en el directorio raíz de prestashop. 

4. Ingresar al Administrador de tienda de PrestaShop.

5. En el Menú Módulos seleccionar Módulos:

  ![1](https://cloud.githubusercontent.com/assets/24914148/25497145/fb4e84b4-2b59-11e7-855f-aa7f03ac9818.png)

6. En categorías seleccionar Plataforma de pago:

  ![2](https://cloud.githubusercontent.com/assets/24914148/25497146/fb5a29cc-2b59-11e7-8293-a3e4babac965.png)

7. Al traer el resultado se puede ver el módulo NPS

 ![3](https://cloud.githubusercontent.com/assets/24914148/25497147/fb7b3964-2b59-11e7-8f65-052c30cca726.png)

8. Al finalizar la instalación verán la siguiente pantalla:

![4](https://cloud.githubusercontent.com/assets/24914148/25497148/fb80d98c-2b59-11e7-89aa-fea05c2df69c.png)

9.	Configurar con los datos que corresponda:    
  Metodología de Pago: PayOnline_3p ó Authorize_3p / Capture.   
  Completar todos los datos con los provistos por altas@nps.com.ar y presionar SALVAR.
  ![5](https://cloud.githubusercontent.com/assets/24914148/25497149/fb8f0f5c-2b59-11e7-8358-ad93fdbe80d7.png)

  Ejemplo:    
  Comercio Email: mail@mail.com   
  Identificacion del Comerciante: test    
  URL Servicio Web: https://implementacion.nps.com.ar/ws.php?wsdl   
  Clave Secreta: mf7mw2Aal9ozRkrbYD9asZ7mGKx4t7LfmQPgSZHBg3A7nziJCrt5Q0rgLnkCu3pe

## Configuraciones Avanzadas

En esta sección se explicará cómo configurar la moneda, el país, y las cuotas.

1. Configuración de Moneda:   
  Seleccionar “Menú” / “Localización”/ “Moneda”

  ![6](https://cloud.githubusercontent.com/assets/24914148/25497136/fb0eb6ae-2b59-11e7-87ce-b946f0fe7279.png)

  Aquí se podrá configurar la moneda del país con el cuál se va a operar, por ejemplo en argentina los parámetros son :   
  Argentina = ARG   ,  Pesos Argentinos = 032.

  Como ya está creado lo modificamos con los valores correctos, ya que por default Argentina figura como ARS y no ARG y la moneda 32 en vez de 032.

  ![7](https://cloud.githubusercontent.com/assets/24914148/25497135/fb0eb780-2b59-11e7-9b25-7901d5f31dec.png)

2. Configuración de Países

  ![8](https://cloud.githubusercontent.com/assets/24914148/25497137/fb102322-2b59-11e7-98c6-e127ac203503.png)

  Se pueden agregar o Modificar países.

  Ejemplo Modificación de País Argentina:

  ![9](https://cloud.githubusercontent.com/assets/24914148/25497138/fb12bfec-2b59-11e7-871e-bc76425b81d4.png)
  ![10](https://cloud.githubusercontent.com/assets/24914148/25497139/fb16eeb4-2b59-11e7-98ef-f1ee0fcbeeab.png)
  ![11](https://cloud.githubusercontent.com/assets/24914148/25497140/fb1ec968-2b59-11e7-964c-d21fbfd647b4.png)

3. Configuración de Cuotas    
  Menú: NPS / Installments (En caso de no poder ver la pantalla correctamente (imagen debajo), realizar el paso 4 y luego volver al paso 3)

  ![12](https://cloud.githubusercontent.com/assets/24914148/25497142/fb45bb4a-2b59-11e7-9b7f-304d30e87513.png)

  Se desplegará la siguiente pantalla:(En caso de no poder ver la pantalla correctamente (imagen debajo), realizar el paso 4 y luego volver al paso 3)

  ![13](https://cloud.githubusercontent.com/assets/24914148/25497141/fb455b28-2b59-11e7-9dba-199dc92c1b69.png)

  Para añadir un nuevo plan de cuotas presionar en “Añadir nuevo”   

  Configuración de nuevo plan de cuotas:
  +	Seleccionan el Producto
  + Ingresan las cuotas, ejemplo 1
  + Ingresan el porcentaje de interés o “0” si no tiene interés.
  + Guardar

  ![14](https://cloud.githubusercontent.com/assets/24914148/25497143/fb484e32-2b59-11e7-9bf2-8b6b0ec3a14e.png)

4.	Limpiar Caché nuevamente. (Menú: Parámetros Avanzados / Rendimiento / Limpiar la cache Smarty & Autoload:

  ![15](https://cloud.githubusercontent.com/assets/24914148/25497144/fb4aa83a-2b59-11e7-9e76-ad61298853c9.png)
