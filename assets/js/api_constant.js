var origin  = window.location.origin;
var baseUrl = `${origin}/coffee_shop_pos`;


// auth api
var loginApi            = `${baseUrl}/auth/authenticate`;
var deautnApi            = `${baseUrl}/auth/logout`;
var getMenuApi          = `${baseUrl}/menu/getMenu`;
var addMenuApi          = `${baseUrl}/menu/addMenu`;
var updateMenuApi       = `${baseUrl}/menu/updateMenu`;
var getMenuTypeApi      = `${baseUrl}/menu/getMenuType`;
var addMenuTypeApi      = `${baseUrl}/menu/addMenuType`;
var updateMenuTypeApi   = `${baseUrl}/menu/updateMenuType`;
var getSuppliersApi     = `${baseUrl}/supplier/getSuppliers`;
var addSuppliersApi     = `${baseUrl}/supplier/addSuppliers`;
var updateSuppliersApi  = `${baseUrl}/supplier/updateSuppliers`;
var getProductsApi      = `${baseUrl}/product/getProducts`;
var getProductTypeApi   = `${baseUrl}/product/getProductType`;
var getStoresApi        = `${baseUrl}/store/getStores`;
var addProductApi       = `${baseUrl}/product/addProduct`;
var updateProductApi    = `${baseUrl}/product/updateProduct`;
var getStocksApi        = `${baseUrl}/stocks/getStocks`;
var getProductListApi   = `${baseUrl}/stocks/getProductList`;
var getInventoryApi     = `${baseUrl}/inventory/getInventory`;
var getInventoryItemsApi= `${baseUrl}/inventory/getInventoryItems`;
var addStoreApi         = `${baseUrl}/store/addStore`;
var updateStoreApi      = `${baseUrl}/store/updateStore`;
var addProductTypeApi   = `${baseUrl}/product/addProductType`;
var updateProductTypeApi= `${baseUrl}/product/updateProductType`;
var getUsedProducts     = `${baseUrl}/product/getUsedProducts`;
var addTransactionApi   = `${baseUrl}/transaction/addTransaction`;
var getTransactionApi   = `${baseUrl}/transaction/getTransaction`;
var addEndTransactionApi   = `${baseUrl}/transaction/addEndOfDayTransaction`;
var getEndTransactionApi   = `${baseUrl}/transaction/getEndTransaction`;
var getDiscountsApi        = `${baseUrl}/discounts/getDiscounts`;
