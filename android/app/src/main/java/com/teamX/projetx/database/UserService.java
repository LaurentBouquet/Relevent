package com.teamX.projetx.database;

import com.teamX.projetx.user.User;

import retrofit2.Call;
import retrofit2.http.GET;
import retrofit2.http.Header;
import retrofit2.http.Multipart;
import retrofit2.http.POST;
import retrofit2.http.Part;
import retrofit2.http.Path;

/**
 * Created by Paul on 14/07/2017.
 */

public interface UserService {

    enum JSONResponses{
        USER_KEY_NOT_FOUND,
        USER_CREATE_FAILED,
        USER_NICKNAME_EXISTED,
        USER_MAIL_EXISTED,
        API_KEY_ACCESS_DENIED,
        USER_LOGIN_FAILED,
        USER_CREATED_SUCCESSFULLY,
        USER_LOGIN_SUCCESSFULLY,
        REQUEST_FAILED
    }

    @Multipart
    @POST("user/register")
    Call<String> userRegister(@Part("nickname") String nickname, @Part("mail") String mail, @Part("password") String password);

    @Multipart
    @POST("user/login")
    Call<User> userLogin(@Part("nickname") String nickname, @Part("password") String password);

    @GET("user/getDataById/{userid}")
    Call<User> userGetDataById(@Header("Authorization") String userKey, @Path("userid") Integer user_id);
}