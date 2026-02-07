// API Configuration
export const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'https://vulnforge-academy.onrender.com';

// API Endpoints
export const API_ENDPOINTS = {
    // Health
    health: `${API_BASE_URL}/api/health`,

    // Levels
    levels: `${API_BASE_URL}/api/levels`,

    // Authentication
    login: `${API_BASE_URL}/api/auth/login`,
    register: `${API_BASE_URL}/api/auth/register`,
    me: `${API_BASE_URL}/api/auth/me`,
    users: `${API_BASE_URL}/api/auth/users`,

    // SQLi Levels
    sqliLevel1: `${API_BASE_URL}/api/levels/sqli/level1`,
    sqliLevel2: `${API_BASE_URL}/api/levels/sqli/level2`,
    sqliLevel3: `${API_BASE_URL}/api/levels/sqli/level3`,

    // XSS Levels
    xssLevel4: `${API_BASE_URL}/api/levels/xss/level4`,
    xssLevel5: `${API_BASE_URL}/api/levels/xss/level5`,
    xssLevel6: `${API_BASE_URL}/api/levels/xss/level6`,

    // IDOR Levels
    idorLevel7: (userId: number) => `${API_BASE_URL}/api/levels/idor/level7/user/${userId}`,
    idorLevel8: (orderId: number) => `${API_BASE_URL}/api/levels/idor/level8/order/${orderId}`,
    idorLevel9: `${API_BASE_URL}/api/levels/idor/level9/file`,

    // SSRF Levels
    ssrfLevel13: `${API_BASE_URL}/api/levels/ssrf/level13`,
    ssrfLevel14: `${API_BASE_URL}/api/levels/ssrf/level14`,
    ssrfLevel15: `${API_BASE_URL}/api/levels/ssrf/level15`,

    // File Upload Levels
    uploadLevel16: `${API_BASE_URL}/api/levels/upload/level16`,
    uploadLevel17: `${API_BASE_URL}/api/levels/upload/level17`,
    uploadLevel18: `${API_BASE_URL}/api/levels/upload/level18`,

    // RCE Levels
    rceLevel19: `${API_BASE_URL}/api/levels/rce/level19`,
    rceLevel20: `${API_BASE_URL}/api/levels/rce/level20`,

    // Flags
    verifyFlag: `${API_BASE_URL}/api/flags/verify`,
    getHint: (levelId: number) => `${API_BASE_URL}/api/flags/hint/${levelId}`,
};

// API Helper Functions
export async function fetchAPI<T>(endpoint: string, options?: RequestInit): Promise<T> {
    const response = await fetch(endpoint, {
        ...options,
        headers: {
            'Content-Type': 'application/json',
            ...options?.headers,
        },
    });

    if (!response.ok) {
        throw new Error(`API Error: ${response.status}`);
    }

    return response.json();
}

// Specific API calls
export async function getLevels() {
    return fetchAPI<{ levels: any[] }>(API_ENDPOINTS.levels);
}

export async function verifyFlag(levelId: number, flag: string) {
    return fetchAPI<{ success: boolean; message: string }>(API_ENDPOINTS.verifyFlag, {
        method: 'POST',
        body: JSON.stringify({ level_id: levelId, flag }),
    });
}

export async function getHint(levelId: number) {
    return fetchAPI<{ level_id: number; hint: string }>(API_ENDPOINTS.getHint(levelId));
}

export async function login(username: string, password: string) {
    return fetchAPI<{ access_token: string; token_type: string }>(API_ENDPOINTS.login, {
        method: 'POST',
        body: JSON.stringify({ username, password }),
    });
}

export async function checkHealth() {
    return fetchAPI<{ status: string; message: string }>(API_ENDPOINTS.health);
}
