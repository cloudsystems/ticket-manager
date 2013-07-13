create table bugtracker.proj_user_area_table
(area_id integer not null,
user_id integer not null,
project_id integer not null,
creation_date date not null,
updated_date date,
creation_user integer not null,
updated_user integer,
scope integer default 0);


ALTER TABLE bugtracker.proj_user_area_table ADD PRIMARY KEY(area_id,user_id,project_id);


insert into bugtracker.proj_user_area_table(area_id,user_id,project_id,creation_date,creation_user)
values (1,2,10,now(),0);

select * from bugtracker.proj_area_table

insert into bugtracker.proj_user_area_table(area_id,user_id,project_id,creation_date,creation_user)
values (1,2,3,now(),0);
