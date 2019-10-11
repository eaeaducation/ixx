# 接口反馈 BackMsg 码对照表
**编码 描述说明**
* BackMsg_01 参数不能为空
* BackMsg_02 组织标识错误或不存在
* BackMsg_03 工号不存在或密码错误
* BackMsg_04 登录成功
* BackMsg_05 坐席所属帐号被禁用
* BackMsg_06 坐席所属帐号不存在
* BackMsg_07 密码错误
* BackMsg_08 登出成功
* BackMsg_09 坐席工号不存在[坐席工号]
* BackMsg_10 切换队列状态失败!
* BackMsg_11 坐席组 ID 不存在[坐席组 ID]
* BackMsg_12 不存在此座席
* BackMsg_13 变量 type 错误
* BackMsg_14 切换队列状态成功!
* BackMsg_15 帐号不存在
* BackMsg_16 呼叫失败,坐席未签入
* BackMsg_17 呼叫失败,坐席当前状态[状态]。若状态为空，参考 BackMsg_16
* BackMsg_18 呼叫失败,坐席当前外呼受限制
* BackMsg_19 呼叫成功
* BackMsg_20 呼叫失败
* BackMsg_21 咨询操作失败!
* BackMsg_22 请不要重复申请拨号!
* BackMsg_23 坐席暂未设置分机信息,无法进行拨号!
* BackMsg_24 未找到坐席,无法进行拨号!
* BackMsg_25 预咨询的坐席未签入
* BackMsg_26 创建咨询方数据失败!
* BackMsg_27 咨询操作成功!
* BackMsg_28 参数错误!
* BackMsg_29 转接失败
* BackMsg_30 坐席组不存在
* BackMsg_31 转接成功
* BackMsg_32 接回操作成功
* BackMsg_33 接回操作失败
* BackMsg_34 会议操作成功
* BackMsg_35 会议操作失败!
* BackMsg_36 挂机失败
* BackMsg_37 挂机成功
* BackMsg_38 当前坐席无匹配的帐号
* BackMsg_39 强插成功
* BackMsg_40 强插失败!
* BackMsg_41 监听成功!
* BackMsg_42 监听失败!
* BackMsg_43 获取成功
* BackMsg_44 获取失败,无匹配的数据
* BackMsg_45 当前话务不能强拆
* BackMsg_46 强拆成功!
* BackMsg_47 强拆失败!
* BackMsg_48 密语成功!
* BackMsg_49 密语失败!
* BackMsg_50 当前团队下无此坐席组
* BackMsg_51 通话暂停，操作失败!
* BackMsg_52 通话暂停，操作成功!
* BackMsg_53 话务继续,操作失败!
* BackMsg_54 话务继续,操作成功!
* BackMsg_55 坐席工号错误或不存在
* BackMsg_56 不能咨询,坐席当前状态为[状态]
* BackMsg_57 暂无坐席组可签入
* BackMsg_58 未签入任何坐席组
* BackMsg_59 话后模式切换成功
* BackMsg_60 坐席组未签入或组内不存在此坐席
* BackMsg_61 结束话后成功
* BackMsg_62 工作模式切换成功
* BackMsg_63 用 usertype=account 方式调用接口,但帐号不存在或密码错误
* BackMsg_64 通话 channel 不存在。
* BackMsg_65 该坐席不是坐席组长
* BackMsg_66 坐席已经处于暂停状态
* BackMsg_67 外拨营销任 ID 错误
* BackMsg_68 不是坐席组长
* BackMsg_69 预拨号数据导入完毕
* BackMsg_70 modeltype 或 model_id 参数定义错误
* BackMsg_71 context 参数不能为空
* BackMsg_72 source 参数定义错误
* BackMsg_73 外呼营销任务不存在
* BackMsg_74 文件标题头与数据表结构不符.
* BackMsg_75 context 参数错误,无法构建 SQL.
* BackMsg_76 文件中缺少必填字段: 字段名称(字段)
* BackMsg_77 重置客户状态成功
* BackMsg_78 更新客户数据成功
* BackMsg_79 清空归属坐席成功
* BackMsg_80 插入预拨号列表成功
* BackMsg_81 数据重复
* BackMsg_82 电话号码重复
* BackMsg_83 新数据导入客户包成功，并且插入预拨号列表成功
* BackMsg_84 新数据导入客户包成功
* BackMsg_85 数据存储错误
* BackMsg_86 仅支持 csv 文件导入
* BackMsg_87 导入任务创建失败
* BackMsg_88 文件上传失败
* BackMsg_89 远程数据文件获取失败
* BackMsg_404 目标文件不存在
* BackMsg_90 坐席未处于话后状态,无需结束话后
* BackMsg_91 工作模式切换失败，坐席组未签入或不存在此坐席组或坐席不属于此坐席组。
* BackMsg_92 无相关通话
* BackMsg_93 坐席组或团队内无任何坐席
* BackMsg_94 无相关状态的坐席
* BackMsg_95 当前处于咨询状态，无法再发起咨询通话
* BackMsg_96 无法进行通话暂停或恢复。不是坐席与客户单独通话状态。
* BackMsg_97 dtmf 参数只允许填写数字、*、#
* BackMsg_98 发送 DTMF 操作失败
* BackMsg_99 发送 DTMF 操作成功
* BackMsg_100 内核连接失败
* BackMsg_101 varname 只允许大写字母,数字,-,_,并且只能以大写字母或数字开头
* BackMsg_102 设置随路数据成功
* BackMsg_103 设置随路数据失败
* BackMsg_104 咨询中不允许转 IVR 操作
* BackMsg_105 会议中不允许释放转
* BackMsg_106 主 IVR 不存在,请检查 ivrexten 参数正确性
* BackMsg_107 IVR 流程错误, 请检查 ivrflow 参数正确性
* BackMsg_108 坐席转 IVR 操作成功
* BackMsg_109 坐席转 IVR 操作失败
* BackMsg_110 分机不存在
* BackMsg_111 需签出后，再进行分机设置
* BackMsg_112 分机设置完毕
* BackMsg_113 帐号数据创建失败
* BackMsg_114 坐席数据创建失败
* BackMsg_115 超出坐席授权数
* BackMsg_116 当前服务器授权错误
* BackMsg_117 App 请求拨号失败
* BackMsg_118 坐席未设置分机信息
* BackMsg_119 无法获取分机注册状态
* BackMsg_120[ip] 已向话机发送请求[话机 ip 地址]
* BackMsg_121 此分机号码已被其它签入坐席占用
* BackMsg_122[type] 不支持此 yealink 话机型号[话机型号]
* BackMsg_123 坐席不属于任何坐席组
* BackMsg_124 座席数据删除成功
* BackMsg_125 坐席处于话务中, 不能从组内移除
* BackMsg_126 坐席成功从坐席组中移除