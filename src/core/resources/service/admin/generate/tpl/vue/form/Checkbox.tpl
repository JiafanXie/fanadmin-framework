<el-form-item label="t('{LCASE_COLUMN_NAME}')"  {PROP}>
    <el-checkbox-group v-model="formData.{COLUMN_NAME}" :placeholder="t('{LCASE_COLUMN_NAME}Placeholder')">
        <el-checkbox
            v-for="(item, index) in {DICT_TYPE}"
                 :key="index"
                :label="item.{ITEM_VALUE}">
           {{ item.{ITEM_LABEL} }}
        </el-checkbox>
    </el-checkbox-group>
</el-form-item>
