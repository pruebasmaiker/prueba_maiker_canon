import { Routes } from '@angular/router';
import { ReportesComponent } from './components/reportes/reportes.component';
import { BusquedaComponent } from './components/busqueda/busqueda.component';

export const routes: Routes = [
  { path: 'reportes', component: ReportesComponent },
  { path: 'busqueda', component: BusquedaComponent },
  { path: '', component: BusquedaComponent, pathMatch: 'full' }
];
