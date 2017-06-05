# Prestashop 1.5 Plugin

*Read this in other languages: [English](README.md), [Español](README.es.md)

## Introduction

NPS Ingenico Latam is integrated easily to Prestashop, given the posibility to config the payment methods in simply steps. In just a matter of minutes your shopping cart will be ready to start operating online.

## Availability

Supported & Tested in Prestashop 1.5.0.15 to 1.5.6.2

## Integration Modes

To handle financial transactions NPS Ingenico Latam server supports two mechanisms of integration in Prestashop with the 3 part method:     

•	PayOnline_3p  
•	Authorize_3p / Capture 

Authorize method requires a subsequent capture from administration panel.       
The PayOnline_3p method acts as Authorize_3p / Capture_3p where authorization and capture are performed in the same transaction.        


## Instalación

** To make the following configuration it is necessary to have PrestaShop installed: 

Along with this documentation you will be able to download the module and integrated it to Prestashop.

1. Extract the file NPS.Prestashop.1.6.0.x.Connector.v1.01.006.tar.gz

2. Rename the admin folder with the same name of the admin folder that is in prestashop.

3. Copy four extracted directories into the prestashop root directory.

4. Enter the PrestaShop Store Manager.

5. In Modules menu, select Modules:

  ![1](https://cloud.githubusercontent.com/assets/24914148/25497145/fb4e84b4-2b59-11e7-855f-aa7f03ac9818.png)

6. Into categories list, Select payment platform

  ![2](https://cloud.githubusercontent.com/assets/24914148/25497146/fb5a29cc-2b59-11e7-8293-a3e4babac965.png)

7. now, you can see the NPS module

  ![3](https://cloud.githubusercontent.com/assets/24914148/25497147/fb7b3964-2b59-11e7-8f65-052c30cca726.png)

8.	At the end of the installation you will see the following screen:

  ![4](https://cloud.githubusercontent.com/assets/24914148/25497148/fb80d98c-2b59-11e7-89aa-fea05c2df69c.png)

9.	Configure with corresponding data:   
  Payment Methodology: PayOnline_3p OR Authorize_3p / Capture   
  Complete all data with information provided by Ingenico Latam and Save.   

  ![5](https://cloud.githubusercontent.com/assets/24914148/25497149/fb8f0f5c-2b59-11e7-8358-ad93fdbe80d7.png)

  Example:    
   Comercio Email: mail@mail.com       
   Identificacion del Comerciante: test        
   URL Servicio Web: https://implementacion.nps.com.ar/ws.php?wsdl     
   Clave Secreta: mf7mw2Aal9ozRkrbYD9asZ7mGKx4t7LfmQPgSZHBg3A7nziJCrt5Q0rgLnkCu3pe    


## Advanced Settings

This section will explain how to set currency, country, and installment plans.

1.	Currency Settings:        
  Select “Menu” / “Location”/ “Currency”

  ![6](https://cloud.githubusercontent.com/assets/24914148/25497136/fb0eb6ae-2b59-11e7-87ce-b946f0fe7279.png)

  Here you can configure the country currency in which it will operate, for example in Argentina parameters are:    
  Argentina = ARG   ,  Pesos Argentinos = 032.        
  
  As it is created we modify it with the correct values, since by default Argentina figures like ARG instead of ARS and the currency 32 instead of 032.
  
  ![7](https://cloud.githubusercontent.com/assets/24914148/25497135/fb0eb780-2b59-11e7-9b25-7901d5f31dec.png)

2. Country Settings

  ![8](https://cloud.githubusercontent.com/assets/24914148/25497137/fb102322-2b59-11e7-98c6-e127ac203503.png)

  You can add or modify countries.       
  Example Modification of Country Argentina:
  
  ![9](https://cloud.githubusercontent.com/assets/24914148/25497138/fb12bfec-2b59-11e7-871e-bc76425b81d4.png)
  ![10](https://cloud.githubusercontent.com/assets/24914148/25497139/fb16eeb4-2b59-11e7-98ef-f1ee0fcbeeab.png)
  ![11](https://cloud.githubusercontent.com/assets/24914148/25497140/fb1ec968-2b59-11e7-964c-d21fbfd647b4.png)

3. Installment Settings   
  Menú: NPS / Installments (If you can not see the settings correctly (next picture), perform step 4 and then return to step 3)

  ![12](https://cloud.githubusercontent.com/assets/24914148/25497142/fb45bb4a-2b59-11e7-9b7f-304d30e87513.png)
  
  The following page will be displayed:        
  (If you can not see the settings correctly (next picture), perform step 4 and then return to step 3)
  ![13](https://cloud.githubusercontent.com/assets/24914148/25497141/fb455b28-2b59-11e7-9dba-199dc92c1b69.png)

  By pressing "ADD NEW", you will add a new installments plan   
  installments plan settings:   
  + Select Product  
  + Enter installments quantity, example: 1   
  + Enter the percentage of interest or "0" if you have no interest.  
  + Save    
        
  ![14](https://cloud.githubusercontent.com/assets/24914148/25497143/fb484e32-2b59-11e7-9bf2-8b6b0ec3a14e.png)

4.	Clean Cache again.      
    Menu: Advanced Parameters > Performance > Clean cache

    ![15](https://cloud.githubusercontent.com/assets/24914148/25497144/fb4aa83a-2b59-11e7-9e76-ad61298853c9.png)
