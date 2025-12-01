

export function normalizeAmount(value: string | number): string {
    if (typeof value !== "string") {
        return String(value ?? "");
    }

    // 1) Solo dejamos dígitos, puntos y comas
    let cleaned = value.replace(/[^\d.,]/g, "");

    // 2) Último separador (coma o punto) como separador decimal
    const lastSeparatorIndex = Math.max(
        cleaned.lastIndexOf(","),
        cleaned.lastIndexOf(".")
    );

    let integerPart = "";
    let decimalPart = "";

    if (lastSeparatorIndex === -1) {
        // No hay separador decimal → todo entera, sin miles
        integerPart = cleaned.replace(/[.,]/g, "");
    } else {
        integerPart = cleaned
            .slice(0, lastSeparatorIndex)
            .replace(/[.,]/g, "");
        decimalPart = cleaned.slice(lastSeparatorIndex + 1).replace(/[.,]/g, "");
    }

    // Opcional: limitar a 2 decimales
    if (decimalPart.length > 2) {
        decimalPart = decimalPart.slice(0, 2);
    }

    let normalized = integerPart;
    if (decimalPart.length > 0) {
        normalized += "." + decimalPart;
    }

    return normalized;
}