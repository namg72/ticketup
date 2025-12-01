
/**
 * Formatea una cadena de fecha ISO 8601 a un formato dd/mm/aaaa en español.
 * @param isoString La cadena de fecha ISO (ej: "2025-11-19T09:08:04.000000Z").
 * @returns La fecha formateada (ej: "19/11/2025") o 'N/A'.
 */
import { format } from 'date-fns';
import { es } from 'date-fns/locale';

export const formatDate = (isoString: string | null): string =>{

    if(!isoString){
        return "N/A";
    }

    try {
        const formattedDate = format(new Date(isoString), 'dd-MM-yyyy', { locale: es });
        return formattedDate
        
    } catch (error) {
        return 'Fecha inválida';
    }
}