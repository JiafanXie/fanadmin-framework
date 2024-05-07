{IMPORT}

{BEGIN}
/**
 * 获取{NOTES}列表
 * @param params
 * @returns
 */
export function get{UCASE_NAME}List(params: Record<string, any>) {
    return request.get(`{ROUTE_GROUP_NAME}/{ROUTE_NAME}`, {params})
}

/**
 * 获取{NOTES}详情
 * @param {PK} {NOTES}{PK}
 * @returns
 */
export function get{UCASE_NAME}Info({PK}: number) {
    return request.get(`{ROUTE_GROUP_NAME}/{ROUTE_NAME}/${{PK}}`);
}

/**
 * 添加{NOTES}
 * @param params
 * @returns
 */
export function add{UCASE_NAME}(params: Record<string, any>) {
    return request.post('{ROUTE_GROUP_NAME}/{ROUTE_NAME}', params, { showErrorMessage: true, showSuccessMessage: true })
}

/**
 * 编辑{NOTES}
 * @param {PK}
 * @param params
 * @returns
 */
export function edit{UCASE_NAME}(params: Record<string, any>) {
    return request.put(`{ROUTE_GROUP_NAME}/{ROUTE_NAME}/${params.{PK}}`, params, { showErrorMessage: true, showSuccessMessage: true })
}

/**
 * 删除{NOTES}
 * @param {PK}
 * @returns
 */
export function delete{UCASE_NAME}({PK}: number) {
    return request.delete(`{ROUTE_GROUP_NAME}/{ROUTE_NAME}/${{PK}}`, { showErrorMessage: true, showSuccessMessage: true })
}

{WITH_ROUTE_API}

{END}
