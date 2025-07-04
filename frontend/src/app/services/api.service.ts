import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';

import { Observable } from 'rxjs';
import { catchError } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private baseUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  // Personajes
  getPersonajes(nombre?: string): Observable<any> {
    let url = `${this.baseUrl}/personajes`;
    if (nombre) {
      url += `?nombre=${encodeURIComponent(nombre)}`;
    }
    return this.http.get(url);
  }

  getPersonajeDetalle(id: number): Observable<any> {
    return this.http.get(`${this.baseUrl}/personajes/${id}`);
  }

  // Episodios
  getEpisodios(): Observable<any> {
    return this.http.get(`${this.baseUrl}/episodios`);
  }

  // Locaciones
  getLocaciones(): Observable<any> {
    return this.http.get(`${this.baseUrl}/locaciones`);
  }

  // Reportes
  getReportePersonajesPorFecha(): Observable<any> {
    return this.http.get(`${this.baseUrl}/reportes/personajes-por-fecha`);
  }

  getReportePersonajesPorEpisodio(): Observable<any> {
    return this.http.get(`${this.baseUrl}/reportes/personajes-por-episodio`);
  }

  getReporteLocacionesConPersonajes(): Observable<any> {
    return this.http.get(`${this.baseUrl}/reportes/locaciones-con-personajes`);
  }

  importarPersonaje(data: any): Observable<any> {
    console.log('Enviando datos al backend:', JSON.stringify(data, null, 2));
    const headers = { 'Content-Type': 'application/json' };
    return this.http.post(`${this.baseUrl}/importar-personaje`, data, { headers }).pipe(
      catchError((error) => {
        console.error('Error en importarPersonaje:', error);
        if (error.error) {
          console.error('Detalles del error:', error.error);
        }
        throw error;
      })
    );
  }

  // Rick and Morty API pública
  getPersonajesApi(nombre?: string) {
    let url = 'https://rickandmortyapi.com/api/character';
    if (nombre) {
      url += `/?name=${encodeURIComponent(nombre)}`;
    }
    return this.http.get(url);
  }

  getPersonajeDetalleApi(id: number) {
    return this.http.get(`https://rickandmortyapi.com/api/character/${id}`);
  }

  getEpisodiosApiPorIds(ids: number[]): Observable<any> {
    if (!ids.length) {
      return new Observable(subscriber => {
        subscriber.next([]);
        subscriber.complete();
      });
    }
    const url = `https://rickandmortyapi.com/api/episode/${ids.join(',')}`;
    return this.http.get(url);
  }

  getReportesFlexibles(filtros: any): Observable<any> {
    const params = new URLSearchParams();
    if (filtros.estado) params.append('estado', filtros.estado);
    if (filtros.locacion) params.append('locacion', filtros.locacion);
    if (filtros.personaje_nombre) params.append('personaje_nombre', filtros.personaje_nombre);
    if (filtros.episodio_nombre) params.append('episodio_nombre', filtros.episodio_nombre);
    if (filtros.fecha_inicio) params.append('fecha_inicio', filtros.fecha_inicio);
    if (filtros.fecha_fin) params.append('fecha_fin', filtros.fecha_fin);
    const url = `${this.baseUrl}/reportes?${params.toString()}`;
    return this.http.get(url);
  }

  getEstadosDisponibles(): Observable<any> {
    return this.http.get(`${this.baseUrl}/personajes/estados`);
  }
}