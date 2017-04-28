#Requeriments:
Prestashop versions 1.5.0.15 to 1.5.6.2

#How to install:

1) unzip package and locate admin folder, you must rename de admin folder to match your admin folder name
2) copy the package folder and paste in your prestashop folder root, you must write into your folders
-) clear cache(*)
3) go to modules and payment and gateways, you will find NPS module, then click install
4) configure your NPS module with the data provided by NPS support
5) update ISO currency and ISO country code to much your NPS gateway parameters, e.g: Argentina = ARG, Pesos Argentinos = 032
you can do this by edit your currencies and countries in your localization menu.
6) configure installments to allow customers to pay by credit cards, e.g: pay with (VISA) in (1) installments with (0.00%) rate of Interests.
-) clear cache(*)

(*) you may need to clear the cache and smarty compile by clicking in "advanced parameters" then "performance" and then "Clear Smarty cache & Autoload cache"

#NPS Menu:
you will find a new option in your Prestashop Menu call "NPS" and in there are "Orders" to list payments pending to capture and
"Installments" to configure for credit cards.

#Payment actions:
1) Authorization: customer will pay but the payment will only be efective if the administrator have done the capture of that payment
2) Authorization And Capture: this will avoid you to do the capture, you don need to do nothing.

#How to capture a payment:
To capture a payment go to "NPS" and then click on "Orders", if the status of the payment is "pending capture" you are allow to capture
the payment by clicking on the icon on the right side of the row.
You can capture for the total amount or do a partial capture. Be aware captures are only 1 time allow.

#How to do a refund:
To do a refund by NPS, you need to do it by the NPS backoffice. You can access easily by clicking on your
"NPS" menu and then "Orders", there is a button called "Refund Transactions" and it's a link to the NPS backend.

#What happens if I refund an Order by the Order's edit page?
this action has no impact in NPS. this is an off-line refund and only has impact on your platform.

#How to debug:
Transactions are logged and available under "advanced parameters" then "Logs".
Listed Transactions are available only if there are comunication. If transaction is not on the list 
is becouse there was no comunication between prestashop and NPS.