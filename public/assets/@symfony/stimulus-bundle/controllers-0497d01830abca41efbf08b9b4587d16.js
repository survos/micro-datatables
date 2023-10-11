import controller_0 from '../../@survos/simple-datatables/table_controller.js';
import controller_1 from '../ux-chartjs/controller.js';
import controller_2 from '../../controllers/hello_controller.js';
import controller_3 from '../../controllers/api_controller.js';
export const eagerControllers = {"survos--simple-datatables-bundle--table": controller_0, "symfony--ux-chartjs--chart": controller_1, "hello": controller_2, "api": controller_3};
export const lazyControllers = {"hola": () => import("../../controllers/hola_controller.js")};
export const isApplicationDebug = true;